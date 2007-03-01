<?php
namespace Org\Lin;
class Page{
	//显示分页个数
	public $pageNum = 10;

	//显示条数
	public $size;

	//上一页
	public $upPage;

	//当前页
	public $nowpage;

	//下一页
	public $nextPage;

	//总页数
	public $countPage;

	//总条数
	public $countTotal;

	//当前url
	public $url;
	//参数
	public $urlParams;

	//开始、截止
	public $limit = array('start'=>'','end'=>'');

	public $nowpage_str = 'active';//当前页class
	public $disabled    = 'disabled';//不可点击
	public $str_ul      = '<ul class="pagination no-margin" style="padding-right: 25%%;">%s</ul>';
	public $str_li      = '<li class="%s"><a href="%s">%s</a></li>';
	public $str_li_up   = '<li class="prev %s"><a href="%s"><i class="icon-double-angle-left"></i></a></li>';
	public $str_li_next = '<li class="next %s"><a href="%s"><i class="icon-double-angle-right"></i></a></li>';

	public function __construct($config = array()){
		$this->url       = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		if( !empty($_SERVER['QUERY_STRING']) ){
			$urlParams = $_SERVER['QUERY_STRING'];
			$urlParams = preg_replace("/[?|&]page=[\d]+?|page=[\d]+?", "",$urlParams);
			$urlParams = preg_replace("/[?|&]size=[\d]+?|size=[\d]+?/", "",$urlParams);
			$this->urlParams = $urlParams;
		}
		$this->urlParams = empty($this->urlParams)?'?':'&';
		foreach ($config as $key => $value) {
			if( isset($value) )$this->{$key} = $value;
		}
	}

	public function getPageNum(){
		$pageNum  = ( ($this->pageNum)<($this->countPage) )?$this->pageNum:$this->countPage;
		
		$num      = ceil($pageNum/2);
		$nowpage  = $this->nowpage;
		$arr_page = array($nowpage);
		for ($i=1; $i <$pageNum; $i++) {
			if( ($nowpage-$i)>0 ){
				$arr_page[] = $nowpage-$i;
				if(count($arr_page)==$pageNum)break;
			}
			if( ($nowpage+$i)<=($this->countPage) ){
				$arr_page[] = $nowpage+$i;
				if(count($arr_page)==$pageNum)break;
			}
		}
		asort($arr_page);
		return $arr_page;
	}
	public function getli($arr_page){
		$str_li = '';
		foreach ($arr_page as $value) {
			$nowpage_str = (($this->nowpage)==$value)?$this->nowpage_str:'';
			$url         = ($this->url).$this->urlParams.'page='.$value.'&size='.$this->size;
			$str         = sprintf($this->str_li,$nowpage_str,$url,$value);
			$str_li     .= $str;
		}

		$disabled       = (($this->nowpage)-1==0)?$this->disabled:'';
		$url            = (($this->nowpage)-1==0)?'':($this->url).$this->urlParams.'page='.(($this->nowpage)-1).'&size='.$this->size;
		$str_li_up      = sprintf($this->str_li_up,$disabled,$url);

		$disabled       = (($this->nowpage)>=($this->countPage))?$this->disabled:'';
		$url            = (($this->nowpage)>=($this->countPage))?($this->url).$this->urlParams.'page='.($this->countPage).'&size='.$this->size:($this->url).$this->urlParams.'page='.($this->nowpage).'&size='.$this->size;
		$str_li_next    = sprintf($this->str_li_next,$disabled,$url);

		$str_li         = $str_li_up.$str_li.$str_li_next;
		return $str_li;
	}

	public function getPage(){
		$arr_page = $this->getPageNum();
		if(count($arr_page)==1)return '';
		$li       = $this->getli($arr_page);
		$str      = sprintf($this->str_ul,$li);
		return $str;
	}

	
		// <li class="prev disabled">
		// 	<a href="#">
		// 		<i class="icon-double-angle-left"></i>
		// 	</a>
		// </li>

		// <li class="active">
		// 	<a href="#">1</a>
		// </li>

		// <li>
		// 	<a href="#">2</a>
		// </li>

		// <li>
		// 	<a href="#">3</a>
		// </li>

		// <li class="next">
		// 	<a href="#">
		// 		<i class="icon-double-angle-right"></i>
		// 	</a>
		// </li>

}