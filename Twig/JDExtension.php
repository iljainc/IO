<?php
class Project_Twig_Extension extends Twig_Extension
{
  public function getFilters()
  {
    return array(
      'random' => new Twig_Filter_Method($this, 'random'),
    );
  }
  
  public function random($string)
  {
    return mt_rand ( 0 , $string );
  }
  
  public function getName()
  {
    return 'JD';
  }
}
?>
