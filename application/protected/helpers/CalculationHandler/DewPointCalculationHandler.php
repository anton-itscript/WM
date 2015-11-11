<?php

/*
 * Extending for CalculationHandler
 * Serves features and methods for Dew Point calculation
 */

class DewPointCalculationHandler extends CalculationHandler
{
    var $measurements = array(
        array('variable_name' => 'temperature', 'display_name' => 'Temperature', 'required' => 1, 'metric' => 'celsius'),
        array('variable_name' => 'humidity',    'display_name' => 'Humidity',    'required' => 1, 'metric' => 'percent'),
        array('variable_name' => 'pressure',    'display_name' => 'Pressure',    'required' => 0, 'metric' => 'hpascal')
    );
    
    var $formulas = array('simple', 'complex');
    
    var $handler_id_code = 'DewPoint';
    var $metric_html_code = '&deg;C';
    var $display_name = 'Dew Point';
    
    public function prepareFormulaParams()
	{
		return parent::prepareFormulaParams();
    }   
    
    public function makeCalculation() {
        
        switch ($this->calculation_details['formula']) 
		{
        	case 'complex':
                $res = $this->makeCalculationComplex();
                
				break;
            default:    
                $res = $this->makeCalculationSimple();
                
				break;
        }
		
        $this->calculation_details['value'] = $res;
        
		return true;
    }
    
    private function makeCalculationSimple()
	{
        if (isset ($this->formula_params['temperature']) && isset ($this->formula_params['humidity'])) 
		{
            $res =  $this->formula_params['temperature'] - (100 - $this->formula_params['humidity']) / 5;
        
			return $res;
        }
		
        return false;
    }
    
    private function makeCalculationComplex()
	{
        if (isset($this->formula_params['pressure']) && isset($this->formula_params['temperature']) && isset($this->formula_params['humidity'])) 
		{
            if ($this->formula_params['pressure'] == 0) 
			{
                return false;
            }
			
            $f_p = 1.0016 + 3.15 * 0.000001 * $this->formula_params['pressure'] - 0.074 / $this->formula_params['pressure'];

            $e_w_p_t = $f_p * 6.112 * exp( 17.62 * $this->formula_params['temperature'] / (243.12 + $this->formula_params['temperature']) );

            $e = $this->formula_params['humidity'] * $e_w_p_t / 100;

            $ln = log( $e / 6.112 * $f_p );

            return 243.12 * $ln / (17.62 - $ln);
        }
        return false;
    }
}
?>