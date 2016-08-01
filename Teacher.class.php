<?php
header("Content-Type:text/html;charset=utf-8");
/*
定义一个“教师类”，并由此类实例化两个“教师对象”。该类至少包括3个属性，3个方法，其中有个方法是“自我介绍”，就能够把自身的所有信息显示出来。
*/
class Teacher {
	protected $name;
	protected $age;
	protected $city;
	public static $count=0;
	function __construct($name,$age,$city){
		$this->name=$name;
		$this->age=$age;
		$this->city=$city;
		self::$count++;
	}
	function __destruct(){
		self::$count--;
	}
	function showInfo(){
		echo "我叫，$this->name <br>年龄，$this->age <br> 来自，$this->city 希望大家喜欢我！";
		//echo "<br>{slef::$count}";
	}
}
$teacherLi=new Teacher("老李",30,"昆仑");
$teacherLi->showInfo();
echo "<hr/>";
$teacherWang=new Teacher("老王",31,'泰山');
$teacherWang->showInfo();
echo "<hr/>";
/*
定义一个“学生类”，并由此类实例化两个“学生对象”。该类包括姓名，性别，年龄等基本信息，并至少包括一个静态属性（表示总学生数）和一个常量，以及包括构造方法和析构方法。该对象还可以调用一个方法来进行“自我介绍”（显示其中的所有属性）。构造方法可以自动初始化一个学生的基本信息，并显示“ｘｘ加入传智，当前有xx个学生”。
*/
class Student{
	private $name;
	private $gender;
	private $age;
	private $city;
	protected $hobby;
	private static $count=1000;
	function __construct($name,$gender,$age,$city,$hobby){
		$this->name=$name;
		$this->gender=$gender;
		$this->age=$age;
		$this->city=$city;
		$this->hobby=$hobby;
		self::$count++;
		echo "{$this->name}加入传智,当前有".self::$count."个学生。。。<br>";
	}
	function __destruct(){
		self::$count--;
	}
	function introduce(){
		echo "<dl>";
			echo "<dt>姓名：</dt>";
				echo "<dd>{$this->name}</dd>";
			echo "<dt>性别：</dt>";
				echo "<dd>{$this->gender}</dd>";
			echo "<dt>年龄：</dt>";
				echo "<dd>{$this->age}</dd>";
			echo "<dt>城市：</dt>";
				echo "<dd>{$this->city}</dd>";
			echo "<dt>爱好：</dt>";
				echo "<dd>{$this->hobby}</dd>";
		echo "</dl>";
	}
}
$stuMi=new Student("小米","女","3","北京","饥饿营销");
$stuDo=new Student("天狗","男","5","北京","电商");
$stuMi->introduce();
echo "<hr>";
$stuDo->introduce();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>作业</title>
</head>
<body>

</body>
</html>