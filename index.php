protected function PropDef()
	{
		if(!CModule::IncludeModule("iblock")) return false;
		if(empty($this->iblockId)) return false;

		$prop = CIBlockProperty::GetList(array("sort"=>"asc", "name"=>"asc"), array("IBLOCK_ID"=>$this->iblockId));
		    while ($prop_fields = $prop->GetNext())
		    {
		    	if($prop_fields['PROPERTY_TYPE'] == 'L'){
		    		$db_enum_list = CIBlockProperty::GetPropertyEnum($prop_fields['CODE'], array("ID"=>"ASC", "SORT"=>"ASC"), array("IBLOCK_ID"=>$this->iblockId));
		    		$ID = array();
		    		$VALUE = array();
					while($ar_enum_list = $db_enum_list->GetNext())
					{
						$ID[] = $ar_enum_list['ID'];
						$VALUE[] = $ar_enum_list['VALUE'];

					}
					
					$prop_fields['VALUE'] = $ID;
					$prop_fields['VALUE_ENUM'] = $VALUE;

		    	}
		    	$arPropDef[$prop_fields['CODE']] = $prop_fields['ID'];
		    }

		if(!$lastId) $lastId = 0;
		$PropDef = $this->send("getDef", "properties", $lastId);
// 		echo '<pre>';
// print_r($PropDef);
		if($PropDef){
			foreach ($PropDef as $vPDef) {
				 // сформируем массив для создания свойства
				$arFields = array();
				$arFields = array(
				     "NAME" => $vPDef['NAME'],
				     "ACTIVE" => $vPDef['ACTIVE'],
				     "SORT" => $vPDef['SORT'],
				     "CODE" => $vPDef['CODE'],
				     "PROPERTY_TYPE" => $vPDef['PROPERTY_TYPE'],
				     "USER_TYPE" => $vPDef['USER_TYPE'],
				     "IBLOCK_ID" => $this->iblockId,
				     "SEARCHABLE" => $vPDef['SEARCHABLE'],
				     "MULTIPLE" => $vPDef['MULTIPLE'],
				     "USER_TYPE_SETTINGS" => $vPDef['USER_TYPE_SETTINGS'],
				     "LIST_TYPE" => $vPDef['LIST_TYPE'],
				     "WITH_DESCRIPTION" => $vPDef['WITH_DESCRIPTION'],
				     "FILTRABLE" => $vPDef['FILTRABLE']
				   );
				if($vPDef['PROPERTY_TYPE'] == 'F'){
					$arFields['PROPERTY_TYPE'] = 'S';
					$arFields['USER_TYPE'] = 'FileMan';
				}elseif($vPDef['PROPERTY_TYPE'] == 'L'){
					foreach ($vPDef["VALUE_ENUM"] as $kENUM => $vENUM) {
					 	$arFields["VALUES"][$kENUM] = array(
							"VALUE" => $vENUM,
							"DEF" => "N",
							"SORT" => $vPDef["VALUE_SORT"][$kENUM],
							"XML_ID" => $vPDef["VALUE_XML_ID"][$kENUM]
							);
					};

				} elseif($vPDef['CODE'] == 'SOLD') {
					$arFields['PROPERTY_TYPE'] = 'L';
					foreach ($vPDef["USER_TYPE_SETTINGS"]["VIEW"] as $kVIEW => $vVIEW) {
					 	$arFields["VALUES"][] = array(
							"VALUE" => $kVIEW,
							"DEF" => "N",
							"SORT" => "100",
							"XML_ID" => $kVIEW
							);
					};
					$arFields['USER_TYPE_SETTINGS'] = '';
					$arFields['USER_TYPE'] = '';
				}else{
					$arFields["VALUE"] = $vPDef["VALUE"];
				};

				if(array_key_exists($vPDef['CODE'], $arPropDef)){
   					$ibp = new CIBlockProperty();
					$ibp->Update($arPropDef[$vPDef['CODE']], $arFields);
				} else {
					$ibp = new CIBlockProperty;
				   	$PropID = $ibp->Add($arFields);
				}

			} 
		}
		// pp($temp);
	//добавим еще нужные нам свойства если нет: ORIGINAL_ID, ORIGINAL_PRICE_BASE, ORIGINAL_PRICE, ORIGINAL_NACENKA
		$originalProps = array(
			"ORIGINAL_ID" 			=> "Оригинальный ID",
			"ORIGINAL_SEC_ID" 		=> "Оригинальный ID раздела",
			// "PREV_PIC"				=> "Картинка анонса",
			// "DETAIL_PIC"			=> "Картинка детальная",
			"ORIGINAL_URL"			=> "Оригинальный URL",
			"IB_PROVIDER"			=> "Поставщик*",
			"COPY"					=> "Копия",
			"E_DATE"				=> "Дата экспорта"
			);
		foreach ($originalProps as $key => $value) {
			if(array_key_exists($key, $arPropDef) == false){
				$arFields = array();
				$arFields = array(
				     "NAME" => $value,
				     "ACTIVE" => 'Y',
				     "SORT" => 10,
				     "CODE" => $key,
				     "PROPERTY_TYPE" => 'N',
				     "IBLOCK_ID" => $this->iblockId,
				     "MULTIPLE" => "N"
				   );

				switch ($key) {
					case 'E_DATE':
						$arFields['PROPERTY_TYPE'] = 'S';
						$arFields['USER_TYPE'] = 'DateTime';
						break;
					case 'COLORS':
						$arFields['PROPERTY_TYPE'] = 'F';
						break;
					// case 'DETAIL_PIC':
					// 	$arFields['PROPERTY_TYPE'] = 'S';
					// 	$arFields['USER_TYPE'] = 'FileMan';
					// 	break;
					case 'ORIGINAL_URL':
						$arFields['PROPERTY_TYPE'] = 'S';
						break;
					case 'IB_PROVIDER':
						$arFields['SORT'] = 7;
						$arFields['PROPERTY_TYPE'] = 'S';
						$arFields["USER_TYPE"] = 'directory';
						$arFields["LIST_TYPE"] = 'L';
						$arFields["USER_TYPE_SETTINGS"] = array("size"=>"1", "width"=>"0", "group"=>"N", "multiple"=>"N", "TABLE_NAME"=>"providers");
						break;
				}


				$ibp = new CIBlockProperty;
			   	$PropID = $ibp->Add($arFields);
			}
		}
	}
