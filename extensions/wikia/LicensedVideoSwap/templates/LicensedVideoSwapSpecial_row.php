<? foreach ($videoList as $video): ?>

<div class="row">
	<span class="swap-arrow lvs-sprite"></span>
	<div class="grid-3 alpha non-premium">
		<div class="posted-in">
			<a class="ellipses" href="#"><?= wfMessage('lvs-posted-in-more')->plain() ?></a>
			<div>
				<? if ( count($video['truncatedList']) ): ?>
					<?= wfMessage('lvs-posted-in-label')->plain() ?>
					<ul>
						<?php foreach( $video['truncatedList'] as $article ) { ?>
							<li><a href=""><?= $article['titleText'] ?></a></li>
						<?php } ?>
					</ul>
				<? else: ?>
					<?= wfMessage('lvs-posted-in-label-none')->plain() ?>
				<? endif; ?>
			</div>
		</div>
		<div class="video-wrapper">
			<a href="<?= $video['fileUrl'] ?>" class="image video">
				<?= $video['videoPlayButton'] ?>
				<img alt="<?= $video['fileTitle'] ?>" src="<?= $video['thumbUrl'] ?>" width="<?= $thumbWidth ?>" height="<?= $thumbHeight ?>" data-video-name="<?= htmlspecialchars($video['fileTitle']) ?>" data-video-key="<?= htmlspecialchars(urlencode($video['title'])) ?>" class="Wikia-video-thumb thumbimage">
				<?= $video['videoOverlay'] ?>
			</a>
		</div>
		<button class="secondary"><?= wfMessage('lvs-button-keep') ?></button>
	</div>
	<div class="grid-3 premium">
		<p><?= wfMessage('lvs-best-match-label')->text() ?></p>
		<div class="video-wrapper">
			<a href="<?= $video['fileUrl'] ?>" class="image video">
				<?= $video['videoPlayButton'] ?>
				<img alt="<?= $video['fileTitle'] ?>" src="<?= $video['thumbUrl'] ?>" width="<?= $thumbWidth ?>" height="<?= $thumbHeight ?>" data-video-name="<?= htmlspecialchars($video['fileTitle']) ?>" data-video-key="<?= htmlspecialchars(urlencode($video['title'])) ?>" class="Wikia-video-thumb thumbimage">
				<?= $video['videoOverlay'] ?>
			</a>
		</div>
		<a class="more-link" href="#"><?= wfMessage('lvs-more-suggestions')->numParams(5)->text() ?></a>
		<button class="swap-button lvs-sprite"> <?= wfMessage('lvs-button-swap') ?></button>
	</div>
	<div class="more-videos">
		<ul>
			<? for ($i = 0; $i < 5; $i++): ?>
				<li>
					<a href="<?= $video['fileUrl'] ?>" class="video">
						<span class="timer">1:30</span>
						<div class="Wikia-video-play-button"><img class="sprite play small" src=" <?= $wg->BlankImgUrl ?>"></div>
						<img alt="<?= $video['fileTitle'] ?>" src="<?= $video['thumbUrl'] ?>" data-video-name="<?= htmlspecialchars($video['fileTitle']) ?>" data-video-key="<?= htmlspecialchars(urlencode($video['title'])) ?>" class="Wikia-video-thumb thumbimage">
					</a>
					<p><?= $video['fileTitle'] ?></p>
				</li>
			<? endfor; ?>
		</ul>
	</div>
</div>
 <? endforeach; ?>