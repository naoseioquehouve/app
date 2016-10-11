/* jshint ignore:start */ define( 'embeddablediscussions.templates.mustache', [], function() { 'use strict'; return {
    "DiscussionError" : '<p class="error widget-discussions-error">{{errorMessage}}</p>',"DiscussionThreadDesktop" : '<section class="module embeddable-discussions-module"><div class="embeddable-discussions-heading-container"><div class="embeddable-discussions-heading">{{heading}}</div><div class="embeddable-discussions-show-all"><a href="{{discussionsUrl}}">{{showAll}}</a></div></div><div class="embeddable-discussions-threads {{columnsWrapperClass}}" data-requestData="{{requestData}}"data-requestUrl="{{requestUrl}}">{{loading}}</div></section>',"DiscussionThreadMobile" : '<div data-component="widget-discussions" data-attrs="{{mercuryComponentAttrs}}">{{loading}}</div>',"DiscussionThreads" : '{{#threads}}<div class="embeddable-discussions-post-detail {{columnsDetailsClass}}"><div class="embeddable-discussions-side-spaced"><a href="{{link}}" class="embeddable-discussions-fill-div"></a><div id="post-{{id}}" class="embeddable-discussions-header-container"><div class="avatar-container">{{#authorAvatar}}<a href="/wiki/User:{{author}}"><img src="{{authorAvatar}}" class="avatar" alt="{{author}}"></a>{{/authorAvatar}}{{^authorAvatar}}<div class="embeddable-discussions-default-avatar"></div>{{/authorAvatar}}</div><div class="avatar-details"><a class="avatar-username" href="/wiki/User:{{author}}">{{author}}</a><span class="embeddable-discussions-timestamp" title="{{timestamp}}">• {{createdAt}}</span></div></div><div class="embeddable-discussions-title">{{title}}</div><div class="embeddable-discussions-content">{{content}}</div><div class="embeddable-discussions-forum">{{forumName}}</div><div class="embeddable-discussions-post-counters"><span class="embeddable-discussions-post-counter"><svg class="embeddable-discussions-upvote-icon-tiny"></svg>{{upvoteCount}}</span><span class="embeddable-discussions-post-counter"><svg class="embeddable-discussions-reply-icon-tiny"></svg>{{commentCount}}</span></div></div><div><ul class="embeddable-discussions-post-actions"><li class="small-4 large-4 embeddable-discussions-upvote-area">{{#hasUpvoted}}<a href="{{upvoteUrl}}" class="upvote" data-id="{{firstPostId}}" data-hasUpvoted="1"><svg class="embeddable-discussions-upvote-icon-active"></svg>{{upvoteText}}</a>{{/hasUpvoted}}{{^hasUpvoted}}<a href="{{upvoteUrl}}" class="upvote" data-id="{{firstPostId}}" data-hasUpvoted="0"><svg class="embeddable-discussions-upvote-icon"></svg>{{upvoteText}}</a>{{/hasUpvoted}}</li><li class="small-4 large-4 embeddable-discussions-replies-area"><a href="{{link}}"><svg class="embeddable-discussions-reply-icon"></svg>{{replyText}}</a></li><li class="small-4 large-4 embeddable-discussions-share-area"><a href="#" class="share" data-link="{{shareUrl}}" data-title="{{title}}"><svg class="embeddable-discussions-share-icon"></svg>{{shareText}}</a></li></ul></div></div>{{/threads}}{{^threads}}<div class="embeddable-discussions-zero"><svg class="embeddable-discussions-zero-plus"></svg><div class="embeddable-discussions-zero-text"><div>{{zeroText}}</div><div>{{zeroTextDetail}}</div></div></div>{{/threads}}',"ShareModal" : '<div class="embeddable-discussions-sharemodal-heading">{{heading}}</div><div class="embeddable-discussions-sharemodal-icons">{{#icons}}<a target="_blank" href="{{url}}"><div class="embeddable-discussions-sharemodal-icon embeddable-discussions-icon-{{icon}}"></div></a>{{/icons}}</div><div class="embeddable-discussions-sharemodal-buttonrow"><a href="#" class="embeddable-discussions-sharemodal-cancel-button">{{close}}</a></div>',
    "done": "true"
  }; }); /* jshint ignore:end */