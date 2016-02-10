/*global require*/
require([
	'jquery',
	'wikia.window',
	'wikia.abTest',
	'wikia.nirvana',
	'wikia.mustache',
	'ext.wikia.recirculation.templates.mustache',
	'ext.wikia.recirculation.tracker'
], function ($, w, abTest, nirvana, Mustache, templates, tracker) {
	// Currently only showing for English communities
	if (w.wgContentLanguage !== 'en') { return; }

	var $container = $('#mw-content-text');

	function injectInContentWidget($container) {
		var $links = $container.find('a'),
			sections = buildSections($container.find('h2')),
			width,
			firstSuitableSection,
			topTitles;

		// If this page doesn't have enough content (either links or sections) we
		// don't want to show this widget
		if ($links.length < 8 || sections.length < 3) {
			return;
		}

		// The idea is to show links above the first section under an infobox
		width = $container.outerWidth();
		firstSuitableSection = sections.find(function(item, index) {
			return item.width === width;
		});

		if (!firstSuitableSection) {
			return;
		}

		topTitles = findTopTitles($links);

		if (topTitles.length < 3) {
			return;
		}

		nirvana.sendRequest({
			controller: 'ArticlesApi',
			method: 'getDetails',
			format: 'json',
			type: 'get',
			data: {
				titles: topTitles.join(','),
				abstract: 0,
				width: 50,
				height: 50
			},
			callback: renderArticles(firstSuitableSection)
		});
	}

	function renderArticles(section) {
		var placeholderImage = w.wgExtensionsPath + '/wikia/Recirculation/images/placeholder.png';
		tracker.trackImpression('in-content');

		return function (response) {
			var items = [],
				html;

			$.each(response.items, function(index, item) {
				item.thumbnail = item.thumbnail || placeholderImage;
				items.push(item);
			});

			$html = $(Mustache.render(templates['inContent.client'], {
				title: $.msg('recirculation-incontent-title'),
				items: items
			}));

			section.$start.before($html);

			$html.on('mousedown', 'a', function() {
				tracker.trackClick('in-content');
			});
		}
	}

	/**
	 * Build the sections array
	 *
	 * DOM/layout querying: OK
	 * DOM modification:    forbidden
	 *
	 * @param {jQuery} $headers headers dividing the article to sections
	 * @returns {Section[]}
	 */
	function buildSections($headers) {
		var i,
			len,
			sections = [],
			intro,
			$start,
			$end,
			section;

		for (i = 0, len = $headers.length; i < len + 1; i += 1) {
			intro = (i === 0);
			$start = !intro && $headers.eq(i - 1);
			$end = $headers.eq(i);
			section = {
				intro: intro,
				$start: intro ? undefined : $start,
				$end: $end
			};
			section.width = $start && $start.outerWidth();

			sections.push(section);
		}

		return sections;
	}

	function findTopTitles($links) {
		var links = buildLinks($links),
			titles = getSortedKeys(links),
			topTitles = [],
			i = 0;

		while (topTitles.length < 3) {
			if (titles[i]) {
				topTitles.push(titles[i]);
			}
			i ++;
		}

		return topTitles;
	}

	function validLink(element) {
		// Not a link to current article
		if (element.title === w.wgTitle) {
			return false;
		}

		// This is a pretty heavy handed Regex, and it may only work for EN communities
		// but the idea is to not display links to special pages
		if (element.title.match(/\S:\S/)) {
			return false;
		}

		return true;
	}

	function buildLinks($links) {
		var links = [];

		$links.each(function(index, element) {
			if (validLink(element)) {
				links[element.title] = links[element.title] || 0;
				links[element.title] ++;
			}
		});

		return links;
	}

	function getSortedKeys(obj) {
		var keys = [];
		for(var key in obj) {
			keys.push(key);
		}

		var sortedKeys = keys.sort(function(a,b){
			return obj[b] - obj[a];
		});

		return sortedKeys;
	}

	if (abTest.inGroup('RECIRCULATION_INCONTENT', 'YES')) {
		$(document).ready( function() {
			injectInContentWidget($container);
		});
	}
});
