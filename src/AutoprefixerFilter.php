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
use Assetic\Filter\BaseNodeFilter;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class AutoprefixerFilter extends BaseNodeFilter
{

    protected $autoprefixerBin;

    protected $nodeBin;

    protected $cascade = true;

    protected $safe = false;

    public function __construct($autoprefixerBin = '/usr/bin/autoprefixer', $nodeBin = null)
    {
        $this->autoprefixerBin = $autoprefixerBin;
        $this->nodeBin         = $nodeBin;
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
     * @return string
     */
    public function getNodeBin()
    {
        return $this->nodeBin;
    }

    /**
     * @param string $nodeBin
     *
     * @return static
     */
    public function setNodeBin($nodeBin)
    {
        $this->nodeBin = empty($nodeBin) ? null : (string) $nodeBin;
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

        $processBuilder = $this->createProcessBuilder([$this->autoprefixerBin]);

        if ($this->nodeBin) {
            $processBuilder->setPrefix($this->nodeBin);
        }

        // disable cascade
        if (!$this->isCascade()) {
            $processBuilder->add('--no-cascade');
        }
        // enable safe mode
        if ($this->isSafe()) {
            $processBuilder->add('--safe');
        }
        // output to stdout
        $processBuilder->add('-o')->add('-');
        // input file
        $processBuilder->add($input);

        try {
            $process = $processBuilder->getProcess();
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

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function filterDump(AssetInterface $asset)
    {
    }
}
