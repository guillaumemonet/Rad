<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Rad\Composer;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;

/**
 * Description of Manager
 *
 * @author guillaume
 */
class Manager {

    public static function postUpdate(Event $event) {
        $composer = $event->getComposer();
        // do stuff
    }

    public static function postAutoloadDump(Event $event) {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        require $vendorDir . '/autoload.php';
    }

    public static function postPackageInstall(PackageEvent $event) {
        $installedPackage = $event->getOperation()->getPackage();
        // do stuff
    }

    public static function warmCache(Event $event) {
        // make cache toasty
    }

    public static function createProject(Event $event) {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        require $vendorDir . '/autoload.php';
        $args = $event->getArguments();
        if (!isset($args[0])) {
            throw new Exception("Missing Project Name");
        }
        $projectName = \Rad\Utils\StringUtils::camelCase($args[0]);
        mkdir('bin');
        mkdir('cache');
        mkdir('datas');
        mkdir('config');
        mkdir('public');
        mkdir('src/' . $projectName . '/Controllers/Base', null, true);
        mkdir('src/' . $projectName . '/Datas');
        mkdir('src/' . $projectName . '/Middleware');
        mkdir('src/' . $projectName . '/Codec');
        mkdir('assets/templates', null, true);
        mkdir('workers');
        mkdir('logs');
    }

}
