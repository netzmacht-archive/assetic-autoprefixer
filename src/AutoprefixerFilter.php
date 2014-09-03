<?php

/*
 * This file is part of the assetic autoprefixer filter package.
 *
 * (c) 2014 Tristan Lins
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bit3\Assetic\Filter\Autoprefixer;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Filter\BaseProcessFilter;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class AutoprefixerFilter extends BaseProcessFilter
{

    protected $autoprefixerBin;

    private $cascade = true;

    private $safe = false;

    public function __construct($autoprefixerBin = '/usr/bin/autoprefixer')
    {
        $this->autoprefixerBin = $autoprefixerBin;
    }

    /**
     * @return string
     */
    public function getAutoprefixerBin()
    {
        return $this->autoprefixerBin;
    }

    /**
     * @param string $autoprefixerBin
     *
     * @return static
     */
    public function setAutoprefixerBin($autoprefixerBin)
    {
        $this->autoprefixerBin = (string) $autoprefixerBin;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCascade()
    {
        return $this->cascade;
    }

    /**
     * @param boolean $cascade
     *
     * @return static
     */
    public function setCascade($cascade)
    {
        $this->cascade = (bool) $cascade;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSafe()
    {
        return $this->safe;
    }

    /**
     * @param boolean $safe
     *
     * @return static
     */
    public function setSafe($safe)
    {
        $this->safe = (bool) $safe;
        return $this;
    }

    public function filterLoad(AssetInterface $asset)
    {
        $input = tempnam(sys_get_temp_dir(), 'assetic_autoprefixer');
        file_put_contents($input, $asset->getContent());

        $pb = $this->createProcessBuilder();
        $pb->add($this->autoprefixerBin);

        // disable cascade
        if (!$this->isCascade()) {
            $pb->add('--no-cascade');
        }
        // enable safe mode
        if ($this->isSafe()) {
            $pb->add('--safe');
        }
        // output to stdout
        $pb->add('-o')->add('-');
        // input file
        $pb->add($input);

        try {
            $process = $pb->getProcess();
            $process->run();
            unlink($input);

            if (!$process->isSuccessful()) {
                throw FilterException::fromProcess($process)->setInput($asset->getContent());
            }
        } catch (ProcessFailedException $exception) {
            unlink($input);
            throw $exception;
        } catch (ProcessTimedOutException $exception) {
            unlink($input);
            throw $exception;
        }

        $asset->setContent($process->getOutput());
    }

    public function filterDump(AssetInterface $asset)
    {
    }
}
