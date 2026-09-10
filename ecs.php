<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

$header = <<<CODE_SAMPLE
This file is part of the "t3_notifier" Extension for TYPO3 CMS.

For the full copyright and license information, please read the
LICENSE.md file that was distributed with this source code.
CODE_SAMPLE;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/Classes', __DIR__ . '/Tests', __DIR__ . '/Configuration', __DIR__ . '/ecs.php'])
    ->withRules([
        DeclareStrictTypesFixer::class,
        LineLengthFixer::class,
        YodaStyleFixer::class,
        StandaloneLineInMultilineArrayFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
    ])
    ->withConfiguredRule(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ])
    ->withConfiguredRule(HeaderCommentFixer::class, [
        'header' => $header,
        'separate' => 'both',
    ])
    ->withConfiguredRule(GeneralPhpdocAnnotationRemoveFixer::class, [
        'annotations' => ['throws', 'author', 'package', 'group'],
    ])
    ->withConfiguredRule(NoSuperfluousPhpdocTagsFixer::class, [
        'allow_mixed' => true,
    ])
    ->withSets([SetList::PSR_12, SetList::COMMON, SetList::CLEAN_CODE])
    ->withSkip([
        DeclareStrictTypesFixer::class => [__DIR__ . '/**/ext_localconf.php'],
        HeaderCommentFixer::class => [__DIR__ . '/**/ext_localconf.php'],
    ]);
