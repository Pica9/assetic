<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2013 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Test\Filter;

use Assetic\Asset\StringAsset;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\LessphpFilter;

/**
 * @group integration
 * @property LessphpFilter $filter
 */
class LessphpFilterTest extends LessFilterTest
{
    protected function setUp()
    {
        if (!class_exists('lessc')) {
            $this->markTestSkipped('LessPHP is not installed');
        }

        $this->filter = new LessphpFilter();
    }

    public function testPresets()
    {
        $asset = new StringAsset('.foo { color: @bar }');
        $asset->load();

        $this->filter->setPresets(array('bar' => 'green'));
        $this->filter->filterLoad($asset);

        $this->assertContains('green', $asset->getContent(), '->setPresets() to pass variables into lessphp filter');
    }

    public function testFormatterLessjs()
    {
        $asset = new StringAsset('.foo { color: green; }');
        $asset->load();

        $this->filter->setFormatter('lessjs');
        $this->filter->filterLoad($asset);

        $this->assertContains("\n  color", $asset->getContent(), '->setFormatter("lessjs")');
    }

    public function testFormatterCompressed()
    {
        $asset = new StringAsset('.foo { color: green; }');
        $asset->load();

        $this->filter->setFormatter('compressed');
        $this->filter->filterLoad($asset);

        $this->assertContains('color:green', $asset->getContent(), '->setFormatter("compressed")');
    }

    public function testFormatterClassic()
    {
        $asset = new StringAsset('.foo { color: green; }');
        $asset->load();

        $this->filter->setFormatter('classic');
        $this->filter->filterLoad($asset);

        $this->assertContains('{ color:green; }', $asset->getContent(), '->setFormatter("classic")');
    }

    public function testPreserveCommentsTrue()
    {
        $asset = new StringAsset("/* Line 1 */\n.foo { color: green }");
        $asset->load();

        $this->filter->setPreserveComments(true);
        $this->filter->filterLoad($asset);

        $this->assertContains('/* Line 1 */', $asset->getContent(), '->setPreserveComments(true)');
    }

    public function testPreserveCommentsFalse()
    {
        $asset = new StringAsset("/* Line 1 */\n.foo { color: green }");
        $asset->load();

        $this->filter->setPreserveComments(false);
        $this->filter->filterLoad($asset);

        $this->assertNotContains('/* Line 1 */', $asset->getContent(), '->setPreserveComments(false)');
    }

    public function testGetChildrenWithFileEnding()
    {
        $file = <<<EOF
@import 'a.less';
@import "b.less";
@import url('c.less');
@import url("d.less");
@import url(e.less);
EOF;

        $children = $this->filter->getChildren(new AssetFactory('/'), $file, __DIR__.'/fixtures/lessphp');
        $this->assertCount(5, $children);
        $this->assertEquals('a.less', $children[0]->getSourcePath());
        $this->assertEquals('b.less', $children[1]->getSourcePath());
        $this->assertEquals('c.less', $children[2]->getSourcePath());
        $this->assertEquals('d.less', $children[3]->getSourcePath());
        $this->assertEquals('e.less', $children[4]->getSourcePath());
    }

    public function testGetChildrenWithoutFileEnding()
    {
        $file = <<<EOF
@import 'a';
@import "b";
@import url('c');
@import url("d");
@import url(e);
EOF;

        $children = $this->filter->getChildren(new AssetFactory('/'), $file, __DIR__.'/fixtures/lessphp');
        $this->assertCount(5, $children);
        $this->assertEquals('a.less', $children[0]->getSourcePath());
        $this->assertEquals('b.less', $children[1]->getSourcePath());
        $this->assertEquals('c.less', $children[2]->getSourcePath());
        $this->assertEquals('d.less', $children[3]->getSourcePath());
        $this->assertEquals('e.less', $children[4]->getSourcePath());
    }
}
