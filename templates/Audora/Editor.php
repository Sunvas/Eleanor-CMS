<?php
/*
	������� ��� ����������

	@var ������������� ���������
	@var HTML ��� ���������
	@var ������ ������ ����� ���������
	@var ������ �������
	@var ������ "�����" BB �����
*/
$id=&$v_0;
$html=&$v_1;
$smiles=&$v_2;
$ownbb=&$v_3;

$l=Eleanor::$Language['editor'];
foreach($smiles as $k=>&$v)
	if($v['show'])
		$v='<a href="#" style="background: transparent url('.$v['path'].') no-repeat 50% 50%;" data-em="'.reset($v['emotion']).'"></a>';
	else
		unset($smiles[$k]);

$obb='';
foreach($ownbb as &$v)
	$obb.='<a href="#" class="bbe_ytext" onclick="EDITOR.Insert(\'['.$v['t'].']\',\''.($v['s'] ? '' : '[/'.$v['t'].']').'\',0,\''.$id.'\'); return false;"'.($v['l'] ? ' title="'.$v['l'].'"' : '').'><span>['.$v['t'].']</span></a>';
$sm=uniqid('sm-');
if($obb or $smiles)
	echo'<div>'.$html
		.($smiles
			? '<div class="bb_footpanel"><b><a href="#" id="a-'.$sm.'" class="bbf_smiles">'.$l['smiles'].'</a></b></div>
<script type="text/javascript">//<![CDATA[
$(function(){
	var D=new DropDown({		selector:"#a-'.$sm.'",
		left:false,
		top:true,
		rel:"#'.$sm.'"
	});
	$("#'.$sm.' a").click(function(){		EDITOR.Insert(" "+$(this).data("em")+" ","'.$id.'");
		D.hide();
		return false;	});
});//]]></script><div class="bb_smiles" id="'.$sm.'" style="position:absolute;display:none;">'.join($smiles).'</div>'
		: '')
		.'<div class="bb_yourpanel">'.$obb.'<div class="clr"></div></div></div>';
else
	echo$html.'<div class="clr"></div>';