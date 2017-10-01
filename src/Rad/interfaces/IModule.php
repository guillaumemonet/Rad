<?php

/*
 * Copyright (C) 2016 Guillaume Monet
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Rad\interfaces;

use Rad\manager\Log;
use Rad\manager\Template;

/**
 * Description of IModule
 *
 * @author Guillaume Monet
 */
abstract class IModule {

    /**
     * 
     */
    public function start() {
	Log::getLogger()->debug("Loading " . self::class);
	if (Template::isCached($this->getModuleTemplate(), $this->getCompileUID(), $this->getCacheUID())) {
	    Log::getLogger()->debug("Cached " . self::class);
	    $this->cache();
	} else {
	    Log::getLogger()->debug("Not Cached " . self::class);
	    $this->nocache();
	}
	$this->other();
    }

    /**
     * call when Module is cached
     */
    protected abstract function cache();

    /**
     * call when Module is not cached
     */
    protected abstract function nocache();

    /**
     * Always call
     */
    protected abstract function other();

    /**
     * Return the module name
     */
    public abstract function getModuleName();

    /**
     * return module templates
     */
    public abstract function getModuleTemplate();

    /**
     * return cache_id for cachable purpose
     */
    public abstract function getCacheUID();

    /**
     * return cache_id for cachable purpose
     */
    public abstract function getCompileUID();

    /**
     * Return the module title
     */
    public abstract function getMetaTitle();

    /**
     * Return the module meta description
     */
    public abstract function getMetaDescription();

    /**
     * Return the canonical url
     */
    public abstract function getCanonicalURL();

    /**
     * Return ariane path
     */
    public abstract function getAriane();

    /**
     * 
     */
    public abstract function setAction();

    /**
     * If module need to be logged-in before display
     * @return boolean
     */
    public function needLogin() {
	return false;
    }

    /**
     * If Module is cachable
     * @return boolean
     */
    public function cacheEnabled() {
	return true;
    }

    public function __toString() {
	return $this->getModuleName();
    }

}

?>
