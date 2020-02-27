<?php

namespace app\models;

use Yii;
use yii\helpers\Html;

trait ModelExtentions
{
    public $success = [];
    public $warnings = [];

    /**
     * Adds a new error to the specified attribute.
     * @param string $attribute attribute name
     * @param string $error new error message
     */
    public function addSuccess($attribute, $success = '')
    {
        $this->success[$attribute][] = $success;
    }

    /**
     * Adds a new error to the specified attribute.
     * @param string $attribute attribute name
     * @param string $error new error message
     */
    public function addWarning($attribute, $warning = '')
    {
        $this->warnings[$attribute][] = $warning;
    }

    public function showSuccess() {
        echo $this->renderMessage($this->success, 'callout-success');
    }

    public function showWarnings() {
        echo $this->renderMessage($this->warnings, 'callout-warning');
    }

    public function showErrors() {
        echo $this->renderMessage($this->errors, 'callout-danger');
    }

    public function showMessages()
    {
        if ($this->success) {
            $this->showSuccess();
        } elseif ($this->errors) {
            $this->showErrors();
        } elseif ($this->warnings) {
            $this->showWarning();
        }
    }

    public function hasMessages() {
        if ($this->success || $this->errors || $this->warnings) {
            return true;
        }

        return false;
    }

    private function renderMessage($m, $class) {
        $html = '';

        foreach ($m as $key => $val) {
            if (is_array($val)) {
                $val = implode('<br>', $val);
            }

            $html .=Html::tag('div', Html::tag('strong', $key) . ($val ? ": $val" : ''));
        }

        return Html::tag('div', $html, ['class' => 'callout' . $class]);
    }
}