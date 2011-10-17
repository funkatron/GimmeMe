<?php
require('vendors/Mustache.php');
require('vendors/Router.php');
require('templates/templates.php');
require('libs/GimmeMe.php');

// config
$config = array();
$config['gb_username']    = 'funkatron';
$config['gb_collection']  = null; // not supported ATM
$config['gb_cache_ttl']   = 15*60; // 15 minutes
$config['templates']      = $templates; // array, comes from templates/templates.php


// Now actually create the GimmeMe class and display
$gm = new GimmeMe($config);
$gm->go();