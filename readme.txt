=== Import Posts From Good Citizen Network ===
Contributors: joyce1987
Tags: synchronize, Good Citizen Network, posts
Requires at least: 5.2.2
Tested up to: 5.2.2
Stable tag: 5.2.2
Requires PHP: 5.2.4

This plugin will synchronize your posts with post you created in Good Citizen Network.

== Description ==

This plugin provides following rest API :

* add post API
* update post API
* delete post API
* get top menu API

If a Good Citizen Network user registered WordPress website domain and token in Good Citizen Network, when he/she creating or updating or deleting posts in Good Citizen Network, these API will be called, the post will synchronize to his/her WordPress blog.

This plugin provides a "GCN Menu" screen, to allow user input their Good Citizen Network token. Good Citizen Network stores user's token as well. Posts could be synchronized only when these token match each other.

== Installation ==

1. Upload the entire `plugin-import-posts-from-gcn` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the Settings->GCN Menu screen to configure the plugin.
4. Visit https://www.goodcitizen.network to create or update or delete posts.

== Frequently Asked Questions ==

= What is GCN Token? =

GCN Token is a string which is the same with the token you input in Good Citizen Network, you can find it in Good Citizen Network -> Profile -> Token For WordPress.
GCN Token is to make sure that you are the owner of this blog.

== Changelog ==

**1.0.0 - July 10, 2019**
Initial release.

**1.0.1 - July 16, 2019**
Add API to get top menu
