<?php

/**
 * Description of SpanColumn
 *
 * 
 */
class SpanColumn extends CDataColumn 
{
	protected $_conditionResult = false;
	
	public $spanCondition;
	public $spanSize = 1;
	public $spanValue;
	
	public $isSpan = false;
	
	protected function checkCondition($row)
	{
		if (!empty($this->spanCondition))
		{
			$prev = ($row - 1 >= 0) ? $this->grid->dataProvider->data[$row - 1] : null;
			$data = $this->grid->dataProvider->data[$row];
			$next = ($row + 1 <= $this->grid->dataProvider->itemCount) ? $this->grid->dataProvider->data[$row + 1] : null;

			$this->_conditionResult = $this->evaluateExpression($this->spanCondition, array('row'=>$row,'data'=>$data, 'prev' => $prev, 'next' => $next));

			if ($this->_conditionResult == true)
			{
				$this->htmlOptions['colspan'] = $this->spanSize;
			}
			else
			{
				unset($this->htmlOptions['colspan']);
			}
		}
	}
	
	protected function renderDataCellContent($row, $data)
	{
		if ($this->isSpan && $this->_conditionResult)
		{
			$value = null;
			
			if($this->spanValue !== null)
			{
				$value = $this->evaluateExpression($this->spanValue,array('data' => $data, 'row' => $row));
			}
    
			echo is_null($value) ? $this->grid->nullDisplay : $this->grid->getFormatter()->format($value, $this->type);
		}
//		else
//		{
			parent::renderDataCellContent($row, $data);
//		}
	}
	
	public function renderDataCell($row)
	{
		$this->checkCondition($row);
		
//		if (!$this->_conditionResult || $this->isSpan)
//		{
			parent::renderDataCell($row);
//		}
	}
}

?>
