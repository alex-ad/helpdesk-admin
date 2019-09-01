/*
 * Excel-Export-Plugin for ExtJS
 * 
 * Copyright soft4tec
 * Date 2014-09-08
 * Version 1.0.0
 * 
 * www.cloud2excel.com
 */
Ext.override(Ext.grid.GridPanel, {
    oExcelExportConfig : {         
        messageText: "Please wait",
        progressText: "Export to Excel..",
        startCell: "A1",
        url: "https://api.cloud2excel.com",
        templatefile: "",
        destinationfile: "export.xlsx",
        noStyle: false
     },

  sendForm: function(inUrl, inData) {
    theForm = Ext.DomHelper.append(document.body, {
      tag: "form",
      method: "post",
      action: inUrl,
      enctype: "application/x-www-form-urlencoded",
      children: [{
          tag: "input",
          type: "hidden",
          name: "data",
          value: ""
        }]
    });
    document.body.appendChild(theForm);
    theForm.data.value = JSON.stringify(inData);
    theForm.submit();
    document.body.removeChild(theForm);
  },

  getExcelCellString2Number: function(inString) {
      var iResult = 0;
      var iNumber = 0;
      var iPower = 0;
      var iStringLength = inString.length;
      for(var i=0; i<iStringLength;i++) {
        iNumber = inString.charCodeAt(i) - 65 + 1;
        iPower = iStringLength - i - 1;
        iResult += iNumber * Math.pow(26,iPower);
      }
      return iResult;
  },

  getNumber2ExcelCellString: function(inNumber) {
      var sResult = "";
      var iNumber = 0;
      var iPower = 0;
      
      //find max power
      while(inNumber / Math.pow(26,iPower) > 1){ iPower ++;}
      if(iPower > 0) iPower -= 1;
      for(var i=iPower; i>=0; i--) {
        iNumber = parseInt(inNumber / Math.pow(26,i));
        sResult += String.fromCharCode(iNumber + 64);
        inNumber = inNumber - (iNumber * Math.pow(26,i)); 
      }
      return sResult;
  }, 

  getCellStringX: function(inStartString, iCount) {
      var sX = inStartString.replace(/[0-9]/g, '');
      return this.getNumber2ExcelCellString(this.getExcelCellString2Number(sX) + iCount);
  },
          
  getCellStringY: function(inStartString, iCount) {
      var iY = parseInt(inStartString.replace( /^\D+/g, ''));
      return (iY + iCount).toString();
  },
          
  getCellStringXY: function(inStartString, ix, iy) {
      return this.getCellStringX(inStartString, ix) + this.getCellStringY(inStartString, iy);
  },        

  
  applyFormating: function(inResultSet, inStyle) {
    //check formating
      if(!this.oExcelExportConfig.noStyle && inStyle ) {
        inResultSet["alignment"] = {};
        inResultSet["font"] = {};
        if(inStyle.alignment) Ext.apply(inResultSet["alignment"], inStyle.alignment);
        if(inStyle.font) Ext.apply(inResultSet["font"], inStyle.font);
        if(inStyle.textAlign) inResultSet["alignment"]["horizontal"] = inStyle.textAlign;
        if(inStyle.verticalAlign) {
            if(inStyle.verticalAlign == 'middle')inStyle.verticalAlign = 'center';
            inResultSet["alignment"]["vertical"] = inStyle.verticalAlign;
        }
        if(inStyle.color) inResultSet["font"]['color'] = inStyle.color.replace('#', '');
        if(inStyle.fontSize) inResultSet["font"]['size'] = inStyle.fontSize;
        if(inStyle.fontWeight) inResultSet["font"]['bold'] = inStyle.fontWeight == "bold";
        if(inStyle.fontStyle) inResultSet["font"]['italic'] = inStyle.fontStyle == "italic";
        if(inStyle.textDecoration) inResultSet["font"]['underline'] = inStyle.textDecoration == "underline" ? "single" : "none";
        if(inStyle.width) inResultSet["width"] = inStyle.width;
        if(inStyle.height) inResultSet["height"] = inStyle.height;
      }  
  },
          

  getColumnHeaderObj: function(inColumnConfig, inStartCell) {
      var oResult =  { 
          "cmd" : "setCellValue",
          "cell" : inStartCell,
          "value" : inColumnConfig.text
      };
      
      var oStyle = inColumnConfig.style;
      if(inColumnConfig.excelHeadStyle) {
          if(oStyle) {
              Ext.apply(oStyle, inColumnConfig.excelHeadStyle);
          } else {
              oStyle = inColumnConfig.excelHeadStyle;
          }
      }

      this.applyFormating (oResult, oStyle);  
      return oResult;

  },
          
  getColumnDataObj: function(inColumnConfig, inStartCell) {
      //inColumnConfig.dataIndex

      var aRows = new Array();
      var aRow = null;
      this.getStore().each(function(oRecord){
            aRow = new Array();
            aRow[0] = oRecord.get(inColumnConfig.dataIndex);
            aRows.push(aRow);
      });        

      var oResult =  {
          "cmd" : "setCellRange",
          "startCell" : inStartCell,
          "values" : aRows
      };

      this.applyFormating (oResult, inColumnConfig.excelRowsStyle);     
      return oResult;

  },          

  countHeaderSubItems: function(inHeaderItems, iLevel, oChildCount) {
    if(iLevel > oChildCount.levels) oChildCount.levels = iLevel;

    inHeaderItems.items.each(function(inItem, index, totalCount){
        iSubItemsCount = inItem.items.getCount();
        if(iSubItemsCount == 0) {
            oChildCount.childs ++;
        } else {
            this.countHeaderSubItems(inItem, iLevel + 1, oChildCount);
        }

    }, this);
  },

  fillHeaderData: function(inHeaderItems, aHeaderData, iy, inColoumnCount) {
    var iSubItemsCount = 0;
    if(iy > inColoumnCount.y) inColoumnCount.y = iy;
      
    inHeaderItems.each(function(inItem, index, totalCount){
        iSubItemsCount = inItem.items.getCount();

        var sCellAddress = this.getCellStringXY(this.oExcelExportConfig.startCell, inColoumnCount.x, iy);
        var aDataRow = this.getColumnHeaderObj(inItem, sCellAddress);
        
        var oChildCount = {"childs" : 0, "levels" : 0};
        this.countHeaderSubItems(inItem, 0, oChildCount);
        if(oChildCount.childs > 0) oChildCount.levels++;
        
        aDataRow['spanX'] = oChildCount.levels
        aHeaderData.push(aDataRow);
        
        if(iSubItemsCount > 0){ 
            this.fillHeaderData(inItem.items, aHeaderData, iy + 1, inColoumnCount);
        } else {
            inColoumnCount.x ++;
        }    
    }, this);
    return iSubItemsCount;
  },

  doExcelExport: function(inConfig) {
    Ext.apply(this.oExcelExportConfig, inConfig);  


    var aMetaData = [];
    var aHeaderData = [];
    var aSpanData = [];
    var aRowsData = [];
    
    //fill headerdata
    var oColoumnCount = {"x" : 0, "y" : 0};
    var oChildCount = {"c" : 0};
    this.fillHeaderData(this.headerCt.items, aHeaderData, 0, oColoumnCount);
    oColoumnCount.x --;             //dec count
   
    //extract span-data
    var aAllCells = [];
    var sCurrentCell = '', sNextY = '';
    for(var i=0; i<aHeaderData.length; i++) aAllCells.push(aHeaderData[i].cell);

    for(var i=0; i<aHeaderData.length; i++){
        sCurrentCell = aHeaderData[i].cell;
        //walk y
        var sSearchY = 0;
        var sMaxY = this.getCellStringY(inConfig.startCell, oColoumnCount.y);
        for (var iCountY = 0; iCountY <= oColoumnCount.y; iCountY++) {
            sSearchY = this.getCellStringY(sCurrentCell, iCountY);
            if(sSearchY >= sMaxY) break;
            sNextY = this.getCellStringX(sCurrentCell, 0) + this.getCellStringY(sCurrentCell, iCountY +1);
            if(aAllCells.indexOf(sNextY) > -1 && sNextY != sCurrentCell ) break;
        }
        var sSearch = this.getCellStringX(sCurrentCell, aHeaderData[i].spanX) + sSearchY;
        if(sSearch != sCurrentCell ) {
            aSpanData.push({
                "cmd" : "mergeCells",
                "cells" : sCurrentCell + ":" +sSearch
            });
        }
 
    }
    
    //prepare data 
    for (var i = 0; i < this.columns.length; i++) {
         aRowsData.push(this.getColumnDataObj(this.columns[i].initialConfig, this.getCellStringXY(this.oExcelExportConfig.startCell, i, oColoumnCount.y + 1)));
    }
    
    
    if(this.oExcelExportConfig.templatefile != "") {
        aMetaData.push({
            "cmd": "useTemplateFile",
            "filename": this.oExcelExportConfig.templatefile
        });
    }
    
    if(this.oExcelExportConfig.destinationfile != "") {
        aMetaData.push({
            "cmd": "setMeta",
            "filename": this.oExcelExportConfig.destinationfile
        });
    }

    var sUrl = this.oExcelExportConfig.url + '/apikey/' + inConfig.apiKey;
    this.sendForm(sUrl , aMetaData.concat(aHeaderData, aSpanData, aRowsData));

    //this._sendForm(oTable);
    Ext.MessageBox.show({
       msg: this.oExcelExportConfig.messageText,
       progressText: this.oExcelExportConfig.progressText,
       width:300,
       wait:true,
       waitConfig: {interval:200},
       animateTarget: 'waitButton'
    });
    setTimeout(function(){
        Ext.MessageBox.hide();
    }, (5 + this.getStore().getCount()) * 100);

  }
});

//dummy for extjs-include
Ext.define("Ext.addon.s4tExcelExportGrid", {
  extend: "Ext.util.Observable",
  singleton: true
});