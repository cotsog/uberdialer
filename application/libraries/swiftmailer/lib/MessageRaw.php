<?php

class Swift_MyMessage extends Swift_Message {
    private $rawContent = '';

    public function setRawContent($rawContent) {
        $this->rawContent = $rawContent;
        return $this;
    }

    public function toString() {
        return $this->rawContent;
    }

    public function toByteStream(Swift_InputByteStream $is) {
        $is->commit();
        $is->write($this->toString());
    }
}
?>