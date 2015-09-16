<?php

namespace Wikia\Service\User\Preferences;

use Doctrine\Common\Cache\CacheProvider;
use Wikia\Domain\User\Preferences\LocalPreference;
use Wikia\Domain\User\Preferences\UserPreferences;
use Wikia\Logger\Loggable;
use Wikia\Persistence\User\Preferences\PreferencePersistence;
use Wikia\Util\WikiaProfiler;

class PreferenceServiceImpl implements PreferenceService {

	use WikiaProfiler;
	use Loggable;

	const CACHE_PROVIDER = "user_preferences_cache_provider";
	const HIDDEN_PREFS = "user_preferences_hidden_prefs";
	const DEFAULT_PREFERENCES = "user_preferences_default_prefs";
	const FORCE_SAVE_PREFERENCES = "user_preferences_force_save_prefs";
	const PROFILE_EVENT = \Transaction::EVENT_USER_PREFERENCES;

	/** @var CacheProvider */
	private $cache;

	/** @var PreferencePersistence */
	private $persistence;

	/** @var UserPreferences[string] */
	private $preferences;

	/** @var string[] */
	private $hiddenPrefs;

	/** @var UserPreferences */
	private $defaultPreferences;

	/** @var string[] */
	private $forceSavePrefs;

	/**
	 * @Inject({
	 *    Wikia\Service\User\Preferences\PreferenceServiceImpl::CACHE_PROVIDER,
	 *    Wikia\Persistence\User\Preferences\PreferencePersistence::class,
	 *    Wikia\Service\User\Preferences\PreferenceServiceImpl::DEFAULT_PREFERENCES,
	 *    Wikia\Service\User\Preferences\PreferenceServiceImpl::HIDDEN_PREFS,
	 *    Wikia\Service\User\Preferences\PreferenceServiceImpl::FORCE_SAVE_PREFERENCES})
	 * @param CacheProvider $cache,
	 * @param PreferencePersistence $persistence
	 * @param UserPreferences $defaultPrefs
	 * @param string[] $hiddenPrefs preferences that fall back to the defaults, whether or not a user has them set
	 * @param string[] $forceSavePrefs
	 */
	public function __construct(
		CacheProvider $cache,
		PreferencePersistence $persistence,
		UserPreferences $defaultPrefs,
		$hiddenPrefs,
		$forceSavePrefs ) {

		$this->cache = $cache;
		$this->persistence = $persistence;
		$this->hiddenPrefs = $hiddenPrefs;
		$this->defaultPreferences = $defaultPrefs;
		$this->forceSavePrefs = $forceSavePrefs;
		$this->preferences = [];
	}

	public function getPreferences( $userId ) {
		return $this->load( $userId );
	}

	/**
	 * forcefully overwrite preferences. this should only be used when correcting errors during migration!
	 * @param $userId
	 * @param UserPreferences $preferences
	 */
	public function setPreferences( $userId, UserPreferences $preferences ) {
		$this->preferences[$userId] = $preferences;
	}

	public function getGlobalPreference( $userId, $name, $default = null, $ignoreHidden = false ) {
		if ( in_array( $name, $this->hiddenPrefs ) && !$ignoreHidden ) {
			return $this->getGlobalDefault( $name );
		}

		$preferences = $this->load( $userId );
		if ( $preferences->hasGlobalPreference( $name ) ) {
			return $preferences->getGlobalPreference( $name );
		}

		return $default;
	}

	public function setGlobalPreference( $userId, $name, $value ) {
		if ( $value == null ) {
			$value = $this->getGlobalDefault( $name );
		}

		$this->load( $userId )->setGlobalPreference( $name, $value );
	}

	public function deleteGlobalPreference( $userId, $name ) {
		$this->load( $userId )->deleteGlobalPreference( $name );
	}

	public function getLocalPreference( $userId, $wikiId, $name, $default = null, $ignoreHidden = false ) {
		if ( in_array( $name, $this->hiddenPrefs ) && !$ignoreHidden ) {
			return $this->getGlobalDefault( $name );
		}

		$preferences = $this->load( $userId );
		if ( $preferences->hasLocalPreference( $name, $wikiId ) ) {
			return $preferences->getLocalPreference( $name, $wikiId );
		}

		return $default;
	}

	public function setLocalPreference( $userId, $wikiId, $name, $value ) {
		if ( $value == null ) {
			$value = $this->getGlobalDefault( $name );
		}

		$this->load( $userId )->setLocalPreference( $name, $wikiId, $value );
	}

	public function deleteLocalPreference( $userId, $name, $wikiId ) {
		$this->load( $userId )->deleteLocalPreference( $name, $wikiId );
	}

	/**
	 * @param string $userId
	 * @return bool
	 * @throws \Exception
	 */
	public function save( $userId ) {
		if ( $userId == 0 ) {
			return false;
		}

		$prefs = $this->load( $userId );
		$prefsToSave = new UserPreferences();

		foreach ( $prefs->getGlobalPreferences() as $pref ) {
			if ( $this->prefIsSaveable( $pref->getName(), $pref->getValue(), $this->getGlobalDefault( $pref->getName() ) ) ) {
				$prefsToSave->setGlobalPreference( $pref->getName(), $pref->getValue() );
			}
		}

		foreach ( $prefs->getLocalPreferences() as $wikiId => $wikiPreferences ) {
			foreach ( $wikiPreferences as $pref ) {
				/** @var $pref LocalPreference */
				if ( $this->prefIsSaveable( $pref->getName(), $pref->getValue(), $this->getLocalDefault( $pref->getName() ) ) ) {
					$prefsToSave->setLocalPreference( $pref->getName(), $pref->getWikiId(), $pref->getValue() );
				}
			}
		}

		if ( !$prefsToSave->isEmpty() ) {
			try {
				$profilerStart = $this->startProfile();
				$result = $this->persistence->save( $userId, $prefsToSave );
				$this->endProfile(
					self::PROFILE_EVENT,
					$profilerStart,
					[
						'user_id' => $userId,
						'method' => 'setPreferences', ] );
				$this->saveToCache( $userId, $prefsToSave );

				return $result;
			} catch ( \Exception $e ) {
				$this->error( $e->getMessage(), ['user' => $userId] );
				throw $e;
			}
		}

		return true;
	}

	public function getGlobalDefault( $pref ) {
		return $this->defaultPreferences->getGlobalPreference( $pref );
	}

	public function getLocalDefault( $pref, $wikiId ) {
		return $this->defaultPreferences->getLocalPreference( $pref, $wikiId );
	}

	/**
	 * @param $userId
	 * @return UserPreferences
	 * @throws \Exception
	 */
	private function load( $userId ) {
		if ( $userId == 0 ) {
			return new UserPreferences();
		}

		if ( !isset( $this->preferences[$userId] ) ) {
			$preferences = $this->loadFromCache( $userId );

			if ( !$preferences ) {
				try {
					$profilerStart = $this->startProfile();
					$preferences = $this->persistence->get( $userId );
					$this->endProfile(
						self::PROFILE_EVENT,
						$profilerStart,
						[
							'user_id' => $userId,
							'method' => 'getPreferences', ] );
				} catch ( \Exception $e ) {
					$this->error( $e->getMessage(), ['user' => $userId] );
					throw $e;
				}
			}

			$this->preferences[$userId] = $this->applyDefaults( $preferences );
		}

		return $this->preferences[$userId];
	}

	private function loadFromCache( $userId ) {
		return $this->cache->fetch( $userId );
	}

	private function saveToCache( $userId, UserPreferences $preferences ) {
		return $this->cache->save( $userId, $preferences );
	}

	private function deleteFromCache( $userId ) {
		return $this->cache->delete( $userId );
	}

	private function applyDefaults( UserPreferences $preferences ) {
		foreach ( $this->defaultPreferences->getGlobalPreferences() as $globalPreference ) {
			if ( !$preferences->hasGlobalPreference( $globalPreference->getName() ) ) {
				$preferences->setGlobalPreference( $globalPreference->getName(), $globalPreference->getValue() );
			}
		}

		foreach ( $this->defaultPreferences->getLocalPreferences() as $wikiId => $localPreferences ) {
			foreach ( $localPreferences as $localPreference ) {
				/** @var LocalPreference $localPreference */
				if ( !$preferences->hasLocalPreference( $localPreference->getName(), $wikiId ) ) {
					$preferences->setLocalPreference( $localPreference->getName(), $wikiId, $localPreference->getValue() );
				}
			}
		}

		return $preferences;
	}

	private function prefIsSaveable( $pref, $value, $valueFromDefaults ) {
		if ( $value == $valueFromDefaults ) {
			return false;
		}

		return in_array( $pref, $this->forceSavePrefs ) || $value != $valueFromDefaults ||
			( $valueFromDefaults != null && $value !== false && $value !== null );
	}
}
