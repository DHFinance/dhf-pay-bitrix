<?php

use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loader::registerNamespace('\Citrus\DHFi', __DIR__ . '/lib');

require_once __DIR__ . '/constants.php';

require_once __DIR__ . '/vendor/autoload.php';
