<?php

namespace Vespolina\Workflow;

use Psr\Log\LoggerInterface;


/**
 * Class Workflow
 * @package Vespolina\Workflow
 */
class Workflow
{
    protected $arcs;
    protected $input;
    protected $logger;
    protected $nodes;
    protected $output;
    protected $tokens;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->input =  new Place();
        $this->input->setName('workflow.input');
        $this->addNode($this->input);
        $this->output =  new Place();
        $this->output->setName('workflow.output');
        $this->addNode($this->output);
    }

    /**
     * Accept the token workflow which initializes the workflow
     *
     * @param TokenInterface $token
     * @return bool
     */
    public function accept(TokenInterface $token)
    {
        $this->addToken($token);
        $this->logger->info('Token accepted into workflow', array('token' => $token));
        if ($this->getInput()->accept($token)) {

            return true;
        }

        return false;
    }

    /**
     * Return the arcs
     *
     * @return mixed
     */
    public function getArcs()
    {
        return $this->arcs;
    }

    public function connect(TokenableInterface $from, TokenableInterface $to)
    {
        if ($from instanceof PlaceInterface && $to instanceof PlaceInterface) {
            throw new \InvalidArgumentException('You can only connect a Vespolina\Workflow\TransactionInterface to a Vespolina\Workflow\PlaceInterface');
        }
        if ($from instanceof TransactionInterface && $to instanceof TransactionInterface) {
            throw new \InvalidArgumentException('You can only connect a Vespolina\Workflow\PlaceInterface to a Vespolina\Workflow\TransactionInterface');
        }
        if (!in_array($from, $this->nodes)) {
            $this->addNode($from);
        }
        if (!in_array($to, $this->nodes)) {
            $this->addNode($to);
        }

        return $this->createArc($from, $to);
    }

    public function createArc(TokenableInterface $from, TokenableInterface $to)
    {
        $arc = new Arc();
        $arc->setFrom($from);
        $arc->setTo($to);
        $this->addNode($arc);

        return $arc;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function addNode(NodeInterface $node)
    {
        $node->setWorkflow($this, $this->logger);
        $this->nodes[] = $node;

        if ($node instanceof ArcInterface) {
            $this->arcs[] = $node;
        }
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function addToken(TokenInterface $token)
    {
        $this->tokens[] = $token;
    }

    /**
     * Return the tokens
     *
     * @return array of \Vespolina\Workflow\TokenInterface
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Remove a token from the collection of tokens
     *
     * @param TokenInterface $token
     */
    public function removeToken(TokenInterface $token)
    {
        foreach ($this->tokens as $key => $curToken) {
            if ($token === $curToken) {
                unset($this->tokens[$key]);

                return;
            }
        }
    }

    public function validateWorkflow()
    {
        $nodes = $this->getNodes();
        $startingNode = $this->getInput();
        $this->traverseNext($startingNode);
    }

    public function traverseNext($node)
    {

    }

}
