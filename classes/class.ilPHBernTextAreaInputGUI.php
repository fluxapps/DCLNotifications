<?php

require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
/**
 * Class ilDclTextArea
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHBernTextAreaInputGUI extends ilTextAreaInputGUI{

	function insert($a_tpl) {
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $this->render());
		$a_tpl->parseCurrentBlock();
	}

	public function render() {
		$ttpl = new ilTemplate("tpl.prop_textarea.html", true, true, "Services/Form");

		// disabled rte
		if ($this->getUseRte() && $this->getDisabled())
		{
			$ttpl->setCurrentBlock("disabled_rte");
			$ttpl->setVariable("DR_VAL", $this->getValue());
			$ttpl->parseCurrentBlock();
		}
		else
		{
			if ($this->getUseRte())
			{
				$rtestring = ilRTE::_getRTEClassname();
				include_once "./Services/RTE/classes/class.$rtestring.php";
				$rte = new $rtestring($this->rteSupport['version']);

				$rte->setInitialWidth($this->getInitialRteWidth());

				// @todo: Check this.
				$rte->addPlugin("emotions");
				foreach ($this->plugins as $plugin)
				{
					if (strlen($plugin))
					{
						$rte->addPlugin($plugin);
					}
				}
				foreach ($this->removeplugins as $plugin)
				{
					if (strlen($plugin))
					{
						$rte->removePlugin($plugin);
					}
				}

				foreach ($this->buttons as $button)
				{
					if (strlen($button))
					{
						$rte->addButton($button);
					}
				}

				$rte->disableButtons($this->getDisabledButtons());

				if($this->getRTERootBlockElement() !== null)
				{
					$rte->setRTERootBlockElement($this->getRTERootBlockElement());
				}

				if (count($this->rteSupport) >= 3)
				{
					$rte->addRTESupport($this->rteSupport["obj_id"], $this->rteSupport["obj_type"], $this->rteSupport["module"], false, $this->rteSupport['cfg_template'], $this->rteSupport['hide_switch']);
				}
				else
				{
					// disable all plugins for mini-tagset
					if(!array_diff($this->getRteTags(), $this->getRteTagSet("mini")))
					{
						$rte->removeAllPlugins();

						// #13603 - "paste from word" is essential
						$rte->addPlugin("paste");

						// #11980 - p-tag is mandatory but we do not want the icons it comes with
						$rte->disableButtons(array("anchor", "justifyleft", "justifycenter",
							"justifyright", "justifyfull", "formatselect", "removeformat",
							"cut", "copy", "paste", "pastetext")); // JF, 2013-12-09
					}

					$rte->addCustomRTESupport(0, "", $this->getRteTags());
				}

				$ttpl->touchBlock("prop_ta_w");
				$ttpl->setCurrentBlock("prop_textarea");
				$ttpl->setVariable("ROWS", $this->getRows());
			}
			else
			{
				$ttpl->touchBlock("no_rteditor");

				if ($this->getCols() > 5)
				{
					$ttpl->setCurrentBlock("prop_ta_c");
					$ttpl->setVariable("COLS", $this->getCols());
					$ttpl->parseCurrentBlock();
				}
				else
				{
					$ttpl->touchBlock("prop_ta_w");
				}

				$ttpl->setCurrentBlock("prop_textarea");
				$ttpl->setVariable("ROWS", $this->getRows());
			}
			if (!$this->getDisabled())
			{
				$ttpl->setVariable("POST_VAR",
					$this->getPostVar());
			}
			$ttpl->setVariable("ID", $this->getFieldId());
			if ($this->getDisabled())
			{
				$ttpl->setVariable('DISABLED','disabled="disabled" ');
			}
			$ttpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
			$ttpl->parseCurrentBlock();
		}

		if ($this->getDisabled())
		{
			$ttpl->setVariable("HIDDEN_INPUT",
				$this->getHiddenTag($this->getPostVar(), $this->getValue()));
		}

		return $ttpl->get();
	}
}