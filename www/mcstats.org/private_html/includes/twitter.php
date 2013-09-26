<?php

require_once 'twitter-php/twitter.class.php';

// @param Twitter
$twitter = null;

loadTwitter();

/**
 * Updates the status, sending a tweet
 *
 * @param $status
 */
function updateStatus($status) {
    global $twitter;

    if ($twitter != null) {
        $twitter->send($status);
    }
}

/**
 * Load the twitter api and connect to it
 */
function loadTwitter() {
    global $twitter, $config;

    if (!$config['twitter']['enabled']) {
        return;
    }

    $twitterConfig = $config['twitter'];
    $twitter = new Twitter($twitterConfig['consumerkey'], $twitterConfig['consumersecret'], $twitterConfig['accesstoken'], $twitterConfig['accesstokensecret']);
}