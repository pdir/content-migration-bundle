<?php

/*
 * Content migration bundle for Contao Open Source CMS
 *
 * Copyright (c) 2020 pdir / digital agentur // pdir GmbH
 *
 * @package    content-migration-bundle
 * @link       https://pdir.de
 * @license    LGPL-3.0+
 * @author     Mathias Arzberger <develop@pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContentMigrationBundle\Tests;

use Pdir\ContentMigrationBundle\PdirContentMigrationBundle;
use PHPUnit\Framework\TestCase;

class PdirContentMigrationBundleTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new PdirContentMigrationBundle();

        $this->assertInstanceOf('Pdir\ContentMigrationBundle\PdirContentMigrationBundle', $bundle);
    }
}
