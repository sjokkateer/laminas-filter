<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\Exception\ExtensionNotLoadedException;
use Laminas\Filter\File\LowerCase as FileLowerCase;
use PHPUnit\Framework\TestCase;
use stdClass;

use function copy;
use function dirname;
use function file_exists;
use function file_get_contents;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class LowerCaseTest extends TestCase
{
    private ?string $testDir  = null;
    private ?string $testFile = null;

    /**
     * Sets the path to test files
     */
    public function setUp(): void
    {
        $source         = dirname(__DIR__) . '/_files/testfile2.txt';
        $this->testDir  = sys_get_temp_dir();
        $this->testFile = sprintf('%s/%s.txt', $this->testDir, uniqid('laminasilter'));
        copy($source, $this->testFile);
    }

    /**
     * Sets the path to test files
     */
    public function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    public function testInstanceCreationAndNormalWorkflow(): void
    {
        self::assertStringContainsString('This is a File', file_get_contents($this->testFile));
        $filter = new FileLowerCase();
        $filter($this->testFile);
        self::assertStringContainsString('this is a file', file_get_contents($this->testFile));
    }

    public function testNormalWorkflowWithFilesArray(): void
    {
        self::assertStringContainsString('This is a File', file_get_contents($this->testFile));
        $filter = new FileLowerCase();
        $filter(['tmp_name' => $this->testFile]);
        self::assertStringContainsString('this is a file', file_get_contents($this->testFile));
    }

    public function testFileNotFoundException(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');
        $filter = new FileLowerCase();
        $filter($this->testFile . 'unknown');
    }

    public function testCheckSettingOfEncodingInIstance(): void
    {
        self::assertStringContainsString('This is a File', file_get_contents($this->testFile));
        try {
            $filter = new FileLowerCase('ISO-8859-1');
            $filter($this->testFile);
            self::assertStringContainsString('this is a file', file_get_contents($this->testFile));
        } catch (ExtensionNotLoadedException $e) {
            self::assertStringContainsString('mbstring is required', $e->getMessage());
        }
    }

    public function testCheckSettingOfEncodingWithMethod(): void
    {
        self::assertStringContainsString('This is a File', file_get_contents($this->testFile));
        try {
            $filter = new FileLowerCase();
            $filter->setEncoding('ISO-8859-1');
            $filter($this->testFile);
            self::assertStringContainsString('this is a file', file_get_contents($this->testFile));
        } catch (ExtensionNotLoadedException $e) {
            self::assertStringContainsString('mbstring is required', $e->getMessage());
        }
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    sprintf('%s/%s.txt', $this->testDir, uniqid()),
                    sprintf('%s/%s.txt', $this->testDir, uniqid()),
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($input): void
    {
        $filter = new FileLowerCase();

        self::assertSame($input, $filter($input));
    }
}
