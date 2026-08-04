<?php

declare(strict_types=1);

namespace Rad\Test\Route;

use PHPUnit\Framework\TestCase;
use Rad\Route\Route;
use Rad\Route\TreeNodeRoute;

/**
 * Regression tests for tree matching, notably backtracking across ambiguous
 * sibling patterns.
 */
final class TreeNodeRouteTest extends TestCase {
    private function node(string $path, string $methodName): Route {
        $route = new Route();
        $route->setPath($path);
        $route->setMethodName($methodName);
        return $route;
    }

    private function segments(string $path): array {
        return explode('/', trim($path, '/'));
    }

    /**
     * A "(?<slug>[a-z0-9\-]+)" node and a deeper "(?<id>[0-9]+)/translations"
     * node both start by matching "2". The slug branch dead-ends, so the tree
     * must backtrack to the numeric branch instead of committing to the first.
     */
    public function testBacktracksAcrossAmbiguousSiblings(): void {
        $tree = new TreeNodeRoute('GET');
        $show = $this->node('/api/pages/(?<slug>[a-z0-9\-]+)', 'show');
        $tr   = $this->node('/api/pages/(?<id>[0-9]+)/translations', 'translations');
        $tree->addFromArray($this->segments($show->getPath()), $show);
        $tree->addFromArray($this->segments($tr->getPath()), $tr);

        $matched = $tree->getRoute($this->segments('/api/pages/2/translations'));
        $this->assertNotNull($matched);
        $this->assertSame('translations', $matched->getMethodName());

        $matchedShow = $tree->getRoute($this->segments('/api/pages/about-us'));
        $this->assertNotNull($matchedShow);
        $this->assertSame('show', $matchedShow->getMethodName());
    }

    public function testUnmatchedPathReturnsNull(): void {
        $tree = new TreeNodeRoute('GET');
        $show = $this->node('/api/pages/(?<id>[0-9]+)', 'show');
        $tree->addFromArray($this->segments($show->getPath()), $show);

        $this->assertNull($tree->getRoute($this->segments('/api/unknown/1')));
    }
}
