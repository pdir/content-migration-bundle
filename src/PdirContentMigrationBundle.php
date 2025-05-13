<?php

declare(strict_types=1);

/*
 * Content migration bundle for Contao Open Source CMS
 *
 * Copyright (c) 2025 pdir / digital agentur // pdir GmbH
 *
 * @package    content-migration-bundle
 * @link       https://pdir.de
 * @license    LGPL-3.0+
 * @author     pdir GmbH <https://pdir.de>
 * @author     Mathias Arzberger <develop@pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContentMigrationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PdirContentMigrationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
