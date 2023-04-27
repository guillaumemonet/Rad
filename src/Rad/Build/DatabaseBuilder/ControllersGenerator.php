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
        $parse->addComment("@get /" . strtolower($mainClass->getName()) . "/");
        $parse->addComment("@produce json");

        $parse->addBody('
                $offset = sprintf(\'%d\',$_GET[\'offset\']);
                $limit = sprintf(\'%d\',$_GET[\'limit\']);
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
        $parse->addComment("@get /" . strtolower($mainClass->getName()) . "/(?<id>[0-9]*)/");
        $parse->addComment("@produce json");
        $parse->addBody('
            $' . $mainClass->getName() . ' = ' . $mainClass->getName() . '::get' . $mainClass->getName() . '($args[\'id\']);
            $response->getBody()->write(json_encode($' . $mainClass->getName() . '));
            return $response;');
    }

    public function generateControllerPostOne(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('post');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment("@post /" . strtolower($mainClass->getName()) . "/");
        $parse->addComment("@produce json");
        $parse->addComment("@consume json");
        $parse->addBody('$' . $mainClass->getName() . ' = new ' . $mainClass->getName() . '();
                $' . $mainClass->getName() . '->hydrate(unserialize($request->getBody()->getContents()));
                $' . $mainClass->getName() . '->create();
                ');
    }

    public function generateControllerPutOne(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('put');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment("@put /" . strtolower($mainClass->getName()) . "/(?<id>[0-9]*)/");
        $parse->addComment("@produce json");
        $parse->addComment("@consume json");
        $parse->addBody('$' . $mainClass->getName() . ' = ' . $mainClass->getName() . '::get' . $mainClass->getName() . '($args[\'id\']);
                $' . $mainClass->getName() . '->hydrate(unserialize($request->getBody()->getContents()));
                $' . $mainClass->getName() . '->update();
                ');
    }

    public function generateControllerPatchOne(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('patch');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment("@patch /" . strtolower($mainClass->getName()) . "/(?<id>[0-9]*)/");
        $parse->addComment("@produce json");
        $parse->addComment("@consume json");
        $parse->addBody('$' . $mainClass->getName() . ' = ' . $mainClass->getName() . '::get' . $mainClass->getName() . '($args[\'id\']);
                $' . $mainClass->getName() . '->hydrate(unserialize($request->getBody()->getContents()));
                $' . $mainClass->getName() . '->update();
                ');
    }

    public function generateControllerDeleteOne(ClassType $mainClass, ClassType $class) {
        $parse = $class->addMethod('delete');
        $parse->setVisibility('public');
        $parse->addParameter('request')->setType('\\Psr\\Http\\Message\\ServerRequestInterface');
        $parse->addParameter('response')->setType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addParameter('args')->setType('array');
        $parse->setReturnType('\\Psr\\Http\\Message\\ResponseInterface');
        $parse->addComment("@delete /" . strtolower($mainClass->getName()) . "/(?<id>[0-9]*)/");
        $parse->addComment("@produce json");
        $parse->addComment("@consume json");
        $parse->addBody('$' . $mainClass->getName() . ' = ' . $mainClass->getName() . '::get' . $mainClass->getName() . '($args[\'id\']);
                $' . $mainClass->getName() . '->delete();
                ');
    }

}
