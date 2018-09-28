<?php

namespace PHPComposter\PHPComposter\PHPMND;

use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Exception;
use PHPComposter\PHPComposter\BaseAction;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Class Action
 *
 * @since 0.1.0
 *
 * @package PHPComposter\PHPComposter\PHPMND
 *
 * @author Pascal Scheepers <pascal@splotch.es>
 */
class Action extends BaseAction
{
    const EXIT_ERRORS_FOUND = 1;
    const EXIT_WITH_EXCEPTIONS = 2;

    const OS_WINDOWS = 'Windows';
    const OS_BSD = 'BSD';
    const OS_DARWIN = 'Darwin';
    const OS_SOLARIS = 'Solaris';
    const OS_LINUX = 'Linux';
    const OS_UNKNOWN = 'Unknown';

    /**
     * Check files to detect for magic numbers
     *
     * @since 0.1.0
     */
    public function runPhpMnd()
    {
        try {
            $arguments = $this->loadPhpMndArguments();

            array_unshift($arguments, $this->root);
            array_unshift($arguments, $this->getPhpMndPath());

            $process = new Process($arguments);
            $process->run();

            $this->write($process->getOutput());

            if ($process->isSuccessful()) {
                $this->success("PHPMND detected no errors, allowing to proceed.", false);
                return;
            }

            $this->error("PHPMND detected errors, aborting commit!" , self::EXIT_ERRORS_FOUND);
        } catch (Exception $e) {
            $this->error('An error occurred trying to run PHPMND: ' . PHP_EOL . $e->getMessage(), self::EXIT_WITH_EXCEPTIONS);
        }
    }

    /**
     * Build the path to the PHPMND binary
     *
     * @return string
     */
    protected function getPhpMndPath()
    {
        $root = FileSystemPath::fromString($this->root);

        $phpMndPath = $root->joinAtomSequence(
            [
                "vendor",
                "bin",
                $this->getPhpMndBinary(),
            ]
        );

        return $phpMndPath->string();
    }

    /**
     * Build the path to the PHPMND configuration
     *
     * @return string
     */
    protected function getPhpMndConfigurationPath()
    {
        $root = FileSystemPath::fromString($this->root);

        $phpMndConfigurationPath = $root->joinAtomSequence(
            [
                "phpmnd.json",
            ]
        );

        return $phpMndConfigurationPath->string();
    }

    /**
     * Return the correct binary for the current OS
     *
     * @return string
     */
    protected function getPhpMndBinary()
    {
        switch (PHP_OS_FAMILY) {
            case self::OS_WINDOWS:
                return "phpmnd.bat";
                break;
            default:
                return "phpmnd";
                break;
        }
    }

    /**
     * Return PHPMND arguments from configuration file
     *
     * @return array
     */
    protected function loadPhpMndArguments()
    {
        $arguments = [];

        if (file_exists($this->getPhpMndConfigurationPath())) {
            $arguments = array_merge($arguments, $this->parseArgumentsFromConfiguration());
        }

        array_push($arguments, '--non-zero-exit-on-violation');

        return $arguments;
    }

    /**
     * Parse arguments from PHPMND configuration file and convert them into command arguments
     *
     * @return array
     */
    protected function parseArgumentsFromConfiguration()
    {
        $configuration = json_decode(file_get_contents($this->getPhpMndConfigurationPath()), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("JSON Decode Error: " . json_last_error_msg());
        }

        $arguments = [];

        if (isset($configuration['ignore-numbers'])) {
            array_push($arguments, '--ignore-numbers=' . implode(',', $configuration['ignore-numbers']));
        }

        if (isset($configuration['ignore-funcs'])) {
            array_push($arguments, '--ignore-funcs=' . implode(',', $configuration['ignore-funcs']));
        }

        if (isset($configuration['exclude-path'])) {
            array_walk($configuration['exclude-path'], function(&$excludePath) {
                $excludePath = "--exclude-path=" . $excludePath;
            });

            $arguments = array_merge($arguments, $configuration['exclude-path']);
        }

        if (isset($configuration['exclude-file'])) {
            array_walk($configuration['exclude-file'], function(&$excludeFile) {
                $excludeFile = "--exclude-file=" . $excludeFile;
            });

            $arguments = array_merge($arguments, $configuration['exclude-file']);
        }

        if (isset($configuration['suffixes'])) {
            array_push($arguments, '--suffixes=' . implode(',', $configuration['suffixes']));
        }

        if (isset($configuration['hint']) && $configuration['hint']) {
            array_push($arguments, '--hint');
        }

        if (isset($configuration['strings']) && $configuration['strings']) {
            if ($configuration['strings'] === true) {
                array_push($arguments, '--strings');
            } else {
                array_push($arguments, '--ignore-strings');
            }
        }

        if (isset($configuration['extensions'])) {
            array_push($arguments, '--extensions=' . implode(',', $configuration['extensions']));
        }

        if (isset($configuration['include-numeric-string']) && $configuration['include-numeric-string']) {
            array_push($arguments, '--include-numeric-string');
        }

        if (isset($configuration['allow-array-mapping']) && $configuration['allow-array-mapping']) {
            array_push($arguments, '--allow-array-mapping');
        }

        return $arguments;
    }
}
