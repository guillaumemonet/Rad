<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Rad\Route;

use Rad\Middleware\Base\Post_SetProduce;
use Rad\Middleware\Base\Pre_CheckConsume;

/**
 * Description of RouteGetterTrait
 *
 * @author guillaume
 */
trait RouteGetterTrait {

    /**
     * 
     * @return array
     */
    public function getMiddlewares() {
        $ret = array();
        foreach ($this->middlewares as $middle) {
            $ret[] = new $middle();
        }
        $ret[] = new Pre_CheckConsume();
        $ret[] = new Post_SetProduce();
        return $ret;
    }

    public function getVerb() {
        return $this->verb;
    }

    public function getClassName() {
        return $this->className;
    }

    public function getMethodName() {
        return $this->methodName;
    }

    public function getRegExp() {
        return $this->regex;
    }

    public function getProcucedMimeType() {
        return $this->produce;
    }

    public function getConsumedMimeType() {
        return $this->consume;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getObservers() {
        return $this->observers;
    }

    /**
     * 
     * @return array
     */
    public function getArgs(): array {
        return $this->args;
    }

}
