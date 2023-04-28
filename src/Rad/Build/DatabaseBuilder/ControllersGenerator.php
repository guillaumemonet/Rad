<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build\DatabaseBuilder;

use Nette\PhpGenerator\ClassType;

/**
 * Description of ControllerGenerator
 *
 * @author Guillaume Monet
 */
class ControllersGenerator extends BaseGenerator {

    public ?string $namespace   = null;
    public ?string $path        = null;
    public ?string $prefix      = null;
    public array $baseRequire = array(
        '\Psr\Http\Message\ServerRequestInterface',
        '\Psr\Http\Message\ResponseInterface',
        'Rad\\Controller\\Controller'
    );

    public function generateControllerGetAll(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('getAll');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment('@get /' . $this->prefix . strtolower($mainClass->getName()) . "/");
        $parse->addComment('@produce json');

        $parse->addBody('
                $offset = isset($_GET[\'offset\']) ? sprintf(\'%d\',$_GET[\'offset\']) : null;
                $limit = isset($_GET[\'limit\']) ? sprintf(\'%d\',$_GET[\'limit\']) : null;
                $datas = ' . $mainClass->getName() . '::getAll($offset,$limit);
               $response->getBody()->write(json_encode($datas));
        return $response;');
    }

    public function generateControllerGetOne(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('get');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment('@get /' . $this->prefix . strtolower($mainClass->getName()) . "/(?<id>[0-9]*)/");
        $parse->addComment('@produce json');
        $parse->addBody('
            $' . strtolower($mainClass->getName()) . ' = ' . $mainClass->getName() . '::get' . $mainClass->getName() . '($args[\'id\']);
            $response->getBody()->write(json_encode($' . strtolower($mainClass->getName()) . '));
            return $response;');
    }

    public function generateControllerPostOne(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('post');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment('@post /' . $this->prefix . strtolower($mainClass->getName()) . "/");
        $parse->addComment('@produce json');
        $parse->addComment('@consume json');
        $parse->addBody('$' . strtolower($mainClass->getName()) . ' = new ' . $mainClass->getName() . '();
                $' . strtolower($mainClass->getName()) . '->hydrate(json_decode($request->getBody()->getContents()));
                $' . strtolower($mainClass->getName()) . '->create();
                $response->getBody()->write(json_encode($' . strtolower($mainClass->getName()) . '));
                return $response;');
    }

    public function generateControllerPutOne(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('put');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment('@put /' . $this->prefix . strtolower($mainClass->getName()) . '/(?<id>[0-9]*)/');
        $parse->addComment('@produce json');
        $parse->addComment('@consume json');
        $parse->addBody('$' . strtolower($mainClass->getName()) . ' = ' . $mainClass->getName() . '::get' . $mainClass->getName() . '($args[\'id\']);
                $' . strtolower($mainClass->getName()) . '->hydrate(json_decode($request->getBody()->getContents()));
                $' . strtolower($mainClass->getName()) . '->update();
                $response->getBody()->write(json_encode($' . strtolower($mainClass->getName()) . '));
                return $response;');
    }

    public function generateControllerPatchOne(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('patch');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment('@patch /' . $this->prefix . strtolower($mainClass->getName()) . '/(?<id>[0-9]*)/');
        $parse->addComment('@produce json');
        $parse->addComment('@consume json');
        $parse->addBody('$' . strtolower($mainClass->getName()) . ' = ' . $mainClass->getName() . '::get' . $mainClass->getName() . '($args[\'id\']);
                $' . strtolower($mainClass->getName()) . '->hydrate(json_decode($request->getBody()->getContents()));
                $' . strtolower($mainClass->getName()) . '->update();
                $response->getBody()->write(json_encode($' . strtolower($mainClass->getName()) . '));
                return $response;');
    }

    public function generateControllerDeleteOne(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('delete');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment('@delete /' . $this->prefix . strtolower($mainClass->getName()) . '/(?<id>[0-9]*)/');
        $parse->addComment('@produce json');
        $parse->addComment('@consume json');
        $parse->addBody('$' . strtolower($mainClass->getName()) . ' = ' . $mainClass->getName() . '::get' . $mainClass->getName() . '($args[\'id\']);
                $' . strtolower($mainClass->getName()) . '->delete();
                $response->getBody()->write(json_encode($' . strtolower($mainClass->getName()) . '));
                return $response;');
    }

}
