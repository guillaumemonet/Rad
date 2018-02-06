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

use Composer\Script\Event;
use Rad\Utils\StringUtils;
use RuntimeException;

/**
 * Description of Manager
 *
 * @author guillaume
 */
abstract class Manager {

    public static function createProject($name = null) {
        if (!isset($name) || $name === null) {
            throw new RuntimeException("Missing RAD project name");
        }
        $serviceName = StringUtils::camelCase($name);
        mkdir('cache');
        mkdir('datas');
        mkdir('config');
        mkdir('public');
        mkdir('src');
        mkdir('src/' . $serviceName);
        mkdir('src/' . $serviceName . '/Controllers');
        mkdir('src/' . $serviceName . '/Controllers/Base');
        mkdir('src/' . $serviceName . '/Datas');
        mkdir('src/' . $serviceName . '/Datas/DAO');
        mkdir('src/' . $serviceName . '/Datas/Imp');
        mkdir('src/' . $serviceName . '/Middlewares');
        mkdir('src/' . $serviceName . '/Codecs');
        mkdir('assets/');
        mkdir('assets/templates');
        mkdir('assets/pictures');
        mkdir('jobs');
        mkdir('log');
        copy(__DIR__ . '/../../../config/config.dist.json', 'config/config.json');
    }

    public static function buildProject($name) {
        
    }

    public static function createService(string $serviceType = null, string $serviceName = null) {
        if ($serviceType === null || $serviceName === null) {
            throw new RuntimeException("Missing RAD service name");
        }
        $serviceType = StringUtils::camelCase($serviceType);
        $serviceName = StringUtils::camelCase($serviceName);
        mkdir('config');
        mkdir('src');
        mkdir('src/Rad');
        mkdir('src/Rad/' . $serviceType);
        if (!file_exists('src/Rad/' . $serviceType . '/' . $serviceType . '.php')) {
            touch('src/Rad/' . $serviceType . '/' . $serviceType . '.php');
        }
        if (!file_exists('src/Rad/' . $serviceType . '/' . $serviceType . 'Interface.php')) {
            touch('src/Rad/' . $serviceType . '/' . $serviceType . 'Interface.php');
        }
        if (!file_exists('src/Rad/' . $serviceType . '/' . $serviceName . '' . $serviceType . 'Handler.php')) {
            touch('src/Rad/' . $serviceType . '/' . $serviceName . '' . $serviceType . 'Handler.php');
        }
        copy(__DIR__ . '/../../../config/config.dist.json', 'config/config.json');
    }

    public static function installConfig(Event $event) {
        $packageName = StringUtils::slugify($event->getComposer()->getPackage()->getName());
        $packageDir = $event->getComposer()->getPackage()->getInstallationSource();
        if (!file_exists('config/' . $packageName . '.json')) {
            copy($packageDir . '/config/config.json', 'config/' . $packageName . '.json');
        } else {
            error_log('Config file already exists, won\'t overwrite');
        }
    }

}
