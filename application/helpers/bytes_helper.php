<?php
	function rand_hex($len)
	{
		$str='';
		srand(time());
		for ($i=0; $i<$len; $i++)
		{
			$c=array(rand(48,57),rand(65,90),rand(97,122));
			$str.=chr($c[rand(0,2)]);
		}
		return $str;
	}
	function salted_hash($pass)
	{
		$salt=rand_hex(50);
		return array('salt'=>$salt,'hash'=>make_hash($salt,$pass));
	}
    function make_hash($salt,$pass)
    {
        return sha1($salt.$pass);
    }