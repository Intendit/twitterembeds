Twitter Embeds
====================

This extension adds five twig functions with named arguments. Use as many or
as few arguments as you like. The arguments map very closely to twitters own
documented arguments, see the following for more info:

https://about.twitter.com/resources/buttons

https://dev.twitter.com/web/embedded-timelines/user

Example:

    {{twitter_share(
        url = "http://example.com",
        text = 'Hey twitterdevs!',
        via = 'TwitterDev',
        large = false,
        related = 'TwitterDev',
        hashtags = 'boltcms'
    )}}

    {{twitter_follow(
        follow = 'TwitterDev',
        show_count = true,
        large = false
    )}}

    {{twitter_hashtag(
        url = "example.com",
        hashtag = 'boltcms',
        text = 'Hey twitterdevs!',
        large = false,
        recommend = 'TwitterDev'
    )}}

    {{twitter_mention(
        screen_name = 'TwitterDev',
        text = 'Hey Twitterdevs!',
        large = false,
        related = 'TwitterDev'
    )}}

The twitter feed function has two modes:

By default it returns prints a twitter widget, this mode*requires* that you
have a widget_id. You can create a widget here: https://twitter.com/settings/widgets
    
    {{twitter_feed(
        screen_name = 'TwitterDev',
        text = 'Tweets by',
        widget_id = '600720083413962752'
    )}}
    
If you on the other hand set the `return_data` argument to true it will instead
query the twitter api and return the data for you to layout/use in your template.

    {% set result = twitter_feed(
        return_data = true,
        screen_name = 'twitter',
        access_token = 'asfsdfhgfjdghk',
        count = 10
    ) %}
    {% if result.error|default(false) %}
        {{ result.error }}
    {% else %}
        {% for tweet in result %}
            <div class="tweet">
                <span class="details">At {{tweet.created_at|localdate()}} @{{tweet.user.screen_name}} tweeted</span>
                <p>{{tweet.text}}</p>
            </div>
        {% endfor %}
    {% endif %}

This requires that you have either set a api_key and api_secret, or that you have
set a access_token. If you set a api_key and api_secret it will generate a access
token for you and save it in bolt's cache for 3 months before requesting a new one.
The keys/tokens can also be set in the main configuration instead of in the template.

    twitter_access_token: ""
    twitter_api_key: ""
    twitter_api_secret: ""

If you want to replace links, mentions and images with their proper counterparts you
can do so like this:

    {% for tweet in result %}
        {% set text = tweet.text %}
        {% for url in tweet.entities.urls %}
            {% set replacetarget = url.url %}
            {% set link = '<a href="' ~ url.expanded_url ~ '">' ~ url.display_url ~ '</a>'%}
            {% set text = text|replace({(replacetarget): link}) %}
        {% endfor %}
        {% for url in tweet.entities.media %}
            {% set replacetarget = url.url %}
            {% set link = '<br><img src="' ~ url.media_url_https ~ '">' %}
            {% set text = text|replace({(replacetarget): link}) %}
        {% endfor %}
        {% for url in tweet.entities.user_mentions %}
            {% set replacetarget = '@' ~ url.screen_name|lower %}
            {% set link = '<a href="https://twitter.com/' ~ url.screen_name ~ '">@' ~ url.screen_name ~ '</a>'%}
            {% set text = text|replace({(replacetarget): link}) %}
        {% endfor %}
        <div class="tweet">
            <h3>{{tweet.created_at|localdate()}}</h3>
            <p>{{text|raw}}</p>
        </div>
    {% endfor %}
