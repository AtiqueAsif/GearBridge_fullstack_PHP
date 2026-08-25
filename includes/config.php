<?php

declare(strict_types=1);

const APP_NAME = 'GearBridge';
const BASE_URL = '/gearbridge';

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'gearbridge_db';
const DB_USER = 'root';
const DB_PASS = '';

const APP_TIMEZONE = 'Asia/Dhaka';

const MAX_ITEM_IMAGE_BYTES = 5 * 1024 * 1024;
const ALLOWED_ITEM_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

date_default_timezone_set(APP_TIMEZONE);
