<?php
/**
 * Copyright (c) 2013, Peter Lind & Kristoffer Mads Sørensen
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution. 
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies, 
 * either expressed or implied, of the FreeBSD Project.
 *
 * PHP version 5.3+
 *
 * @copyright Copyright (c) 2013 Peter Lind & Kristoffer Mads Sørensen
 * @license   https://github.com/Fake51/id-generator/blob/master/LICENSE FreeBSD license
 * @link      https://github.com/Fake51/id-generator
 * @version   0.1
 * @author    Peter Lind <peter.e.lind@gmail.com>
 */

define('BASE_PATH', realpath(__DIR__ . '/../'));
define('VIEWS_PATH', BASE_PATH . "/views/");

define('TEMPLATE_PATH', BASE_PATH . '/templates/');
define('CACHE_PATH', BASE_PATH . '/cache/');
define('OUTPUT_PATH', BASE_PATH . '/output/');
define('INPUT_PATH', BASE_PATH . '/persons/');
define('FONTS_PATH', BASE_PATH . '/fonts/');
define('CONFIG_PATH', BASE_PATH . "/config/config.json");
define('SETTINGS_PATH', BASE_PATH . "/config/settings.json");

// web paths
define('WEB_TEMPLATE_PATH', 'templates/');

// reasonable defaults
define('TEMPLATE_DEFAULT_WIDTH', 1024);
define('TEMPLATE_DEFAULT_HEIGHT', 639);

define('PHOTO_DEFAULT_WIDTH', 319);
define('PHOTO_DEFAULT_HEIGHT', 382);
define('PHOTO_DEFAULT_OFFSET_X', 100);
define('PHOTO_DEFAULT_OFFSET_Y', 20);
define('PHOTO_DEFAULT_ANGLE', 0);

define('NAMEBOX_DEFAULT_WIDTH', round(TEMPLATE_DEFAULT_WIDTH / 4 * 3));
define('NAMEBOX_DEFAULT_HEIGHT', 40);
define('NAMEBOX_DEFAULT_OFFSET_X', 40);
define('NAMEBOX_DEFAULT_OFFSET_Y', 500);
define('NAMEBOX_DEFAULT_ANGLE', 90);

define('FUNCTIONBOX_DEFAULT_WIDTH', round(TEMPLATE_DEFAULT_WIDTH / 4 * 1));
define('FUNCTIONBOX_DEFAULT_HEIGHT', 100);
define('FUNCTIONBOX_DEFAULT_OFFSET_X', 700);
define('FUNCTIONBOX_DEFAULT_OFFSET_Y', 400);
define('FUNCTIONBOX_DEFAULT_ANGLE', 0);
