<?php

declare(strict_types=1);

namespace NassFloPetr\Grabber\Grabbers;

use NassFloPetr\Grabber\Models\Model;
use NassFloPetr\Grabber\Exceptions\SomethingWentChanged;

abstract class DOMDocumentWebGrabber extends WebGrabber
{
    abstract protected function getModel(\DOMNode $DOMNode): Model;

    abstract protected function getDOMNodeList(?string $response = null): \DOMNodeList;

    public function getModels(?Filter $filter = null, ?string $response = null): iterable
    {
        $DOMNodeList = $this->getDOMNodeList($response);

        foreach ($DOMNodeList as $DOMNode) {
            $model = $this->getModel($DOMNode);

            if (!\is_null($filter) && !\call_user_func($filter, $model)) {
                continue;
            }

            yield $model;
        }
    }

    protected function getDOMDocument(?string $response = null): \DOMDocument
    {
        if (\is_null($response)) {
            $response = $this->getResponse();
        }

        $DOMDocument = new \DOMDocument();

        try {
            $DOMDocument->loadHTML($response, \LIBXML_NOERROR);

            if (!$DOMDocument) {
                $error = \libxml_get_last_error();

                throw new SomethingWentChanged(
                    ($error instanceof \libXMLError)
                        ? \serialize($error)
                        : \sprintf('Can\'t create %s object.', \DOMDocument::class)
                );
            }
        } catch (\Exception $e) {
            if ($e->getSeverity() !== \E_WARNING) {
                throw new SomethingWentChanged($e->getMessage());
            }
        }

        return $DOMDocument;
    }

    protected function getDOMDocumentDOMXPath(?\DOMDocument $DOMDocument = null): \DOMXPath
    {
        if (\is_null($DOMDocument)) {
            $DOMDocument = $this->getDOMDocument();
        }

        return new \DOMXPath($DOMDocument);
    }

    protected function getDOMNodeDOMXPath(\DOMNode $DOMNode): \DOMXPath
    {
        return new \DOMXPath($DOMNode->ownerDocument);
    }
}