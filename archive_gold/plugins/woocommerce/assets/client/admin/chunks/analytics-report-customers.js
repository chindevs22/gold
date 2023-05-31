"use strict";(self.webpackChunk_wcAdmin_webpackJsonp=self.webpackChunk_wcAdmin_webpackJsonp||[]).push([[3700],{65373:function(e,o,r){r.r(o),r.d(o,{default:function(){return C}});var t=r(69307),l=r(7862),a=r.n(l),c=r(65736),m=r(22629),s=r(92694),n=r(9818),u=r(67221),i=r(68734);const _=(0,s.applyFilters)("woocommerce_admin_customers_report_filters",[{label:(0,c.__)("Show","woocommerce"),staticParams:["paged","per_page"],param:"filter",showFilters:()=>!0,filters:[{label:(0,c.__)("All Customers","woocommerce"),value:"all"},{label:(0,c.__)("Single Customer","woocommerce"),value:"select_customer",chartMode:"item-comparison",subFilters:[{component:"Search",value:"single_customer",chartMode:"item-comparison",path:["select_customer"],settings:{type:"customers",param:"customers",getLabels:i.jk,labels:{placeholder:(0,c.__)("Type to search for a customer","woocommerce"),button:(0,c.__)("Single Customer","woocommerce")}}}]},{label:(0,c.__)("Advanced filters","woocommerce"),value:"advanced"}]}]),d=(0,s.applyFilters)("woocommerce_admin_customers_report_advanced_filters",{title:(0,c._x)("Customers match {{select /}} filters","A sentence describing filters for Customers. See screen shot for context: https://cloudup.com/cCsm3GeXJbE","woocommerce"),filters:{name:{labels:{add:(0,c.__)("Name","woocommerce"),placeholder:(0,c.__)("Search","woocommerce"),remove:(0,c.__)("Remove customer name filter","woocommerce"),rule:(0,c.__)("Select a customer name filter match","woocommerce"),title:(0,c.__)("{{title}}Name{{/title}} {{rule /}} {{filter /}}","woocommerce"),filter:(0,c.__)("Select customer name","woocommerce")},rules:[{value:"includes",label:(0,c._x)("Includes","customer names","woocommerce")},{value:"excludes",label:(0,c._x)("Excludes","customer names","woocommerce")}],input:{component:"Search",type:"customers",getLabels:(0,i.qc)(u.NAMESPACE+"/customers",(e=>({id:e.id,label:e.name})))}},country:{labels:{add:(0,c.__)("Country / Region","woocommerce"),placeholder:(0,c.__)("Search","woocommerce"),remove:(0,c.__)("Remove country / region filter","woocommerce"),rule:(0,c.__)("Select a country / region filter match","woocommerce"),title:(0,c.__)("{{title}}Country / Region{{/title}} {{rule /}} {{filter /}}","woocommerce"),filter:(0,c.__)("Select country / region","woocommerce")},rules:[{value:"includes",label:(0,c._x)("Includes","countries","woocommerce")},{value:"excludes",label:(0,c._x)("Excludes","countries","woocommerce")}],input:{component:"Search",type:"countries",getLabels:async e=>{const o=(await(0,n.resolveSelect)(u.COUNTRIES_STORE_NAME).getCountries()).map((e=>({key:e.code,label:(0,m.decodeEntities)(e.name)}))),r=e.split(",");return await o.filter((e=>r.includes(e.key)))}}},username:{labels:{add:(0,c.__)("Username","woocommerce"),placeholder:(0,c.__)("Search customer username","woocommerce"),remove:(0,c.__)("Remove customer username filter","woocommerce"),rule:(0,c.__)("Select a customer username filter match","woocommerce"),title:(0,c.__)("{{title}}Username{{/title}} {{rule /}} {{filter /}}","woocommerce"),filter:(0,c.__)("Select customer username","woocommerce")},rules:[{value:"includes",label:(0,c._x)("Includes","customer usernames","woocommerce")},{value:"excludes",label:(0,c._x)("Excludes","customer usernames","woocommerce")}],input:{component:"Search",type:"usernames",getLabels:i.jk}},email:{labels:{add:(0,c.__)("Email","woocommerce"),placeholder:(0,c.__)("Search customer email","woocommerce"),remove:(0,c.__)("Remove customer email filter","woocommerce"),rule:(0,c.__)("Select a customer email filter match","woocommerce"),title:(0,c.__)("{{title}}Email{{/title}} {{rule /}} {{filter /}}","woocommerce"),filter:(0,c.__)("Select customer email","woocommerce")},rules:[{value:"includes",label:(0,c._x)("Includes","customer emails","woocommerce")},{value:"excludes",label:(0,c._x)("Excludes","customer emails","woocommerce")}],input:{component:"Search",type:"emails",getLabels:(0,i.qc)(u.NAMESPACE+"/customers",(e=>({id:e.id,label:e.email})))}},orders_count:{labels:{add:(0,c.__)("No. of Orders","woocommerce"),remove:(0,c.__)("Remove order filter","woocommerce"),rule:(0,c.__)("Select an order count filter match","woocommerce"),title:(0,c.__)("{{title}}No. of Orders{{/title}} {{rule /}} {{filter /}}","woocommerce")},rules:[{value:"max",label:(0,c._x)("Less Than","number of orders","woocommerce")},{value:"min",label:(0,c._x)("More Than","number of orders","woocommerce")},{value:"between",label:(0,c._x)("Between","number of orders","woocommerce")}],input:{component:"Number"}},total_spend:{labels:{add:(0,c.__)("Total Spend","woocommerce"),remove:(0,c.__)("Remove total spend filter","woocommerce"),rule:(0,c.__)("Select a total spend filter match","woocommerce"),title:(0,c.__)("{{title}}Total Spend{{/title}} {{rule /}} {{filter /}}","woocommerce")},rules:[{value:"max",label:(0,c._x)("Less Than","total spend by customer","woocommerce")},{value:"min",label:(0,c._x)("More Than","total spend by customer","woocommerce")},{value:"between",label:(0,c._x)("Between","total spend by customer","woocommerce")}],input:{component:"Currency"}},avg_order_value:{labels:{add:(0,c.__)("AOV","woocommerce"),remove:(0,c.__)("Remove average order value filter","woocommerce"),rule:(0,c.__)("Select an average order value filter match","woocommerce"),title:(0,c.__)("{{title}}AOV{{/title}} {{rule /}} {{filter /}}","woocommerce")},rules:[{value:"max",label:(0,c._x)("Less Than","average order value of customer","woocommerce")},{value:"min",label:(0,c._x)("More Than","average order value of customer","woocommerce")},{value:"between",label:(0,c._x)("Between","average order value of customer","woocommerce")}],input:{component:"Currency"}},registered:{labels:{add:(0,c.__)("Registered","woocommerce"),remove:(0,c.__)("Remove registered filter","woocommerce"),rule:(0,c.__)("Select a registered filter match","woocommerce"),title:(0,c.__)("{{title}}Registered{{/title}} {{rule /}} {{filter /}}","woocommerce"),filter:(0,c.__)("Select registered date","woocommerce")},rules:[{value:"before",label:(0,c._x)("Before","date","woocommerce")},{value:"after",label:(0,c._x)("After","date","woocommerce")},{value:"between",label:(0,c._x)("Between","date","woocommerce")}],input:{component:"Date"}},last_active:{labels:{add:(0,c.__)("Last active","woocommerce"),remove:(0,c.__)("Remove last active filter","woocommerce"),rule:(0,c.__)("Select a last active filter match","woocommerce"),title:(0,c.__)("{{title}}Last active{{/title}} {{rule /}} {{filter /}}","woocommerce"),filter:(0,c.__)("Select registered date","woocommerce")},rules:[{value:"before",label:(0,c._x)("Before","date","woocommerce")},{value:"after",label:(0,c._x)("After","date","woocommerce")},{value:"between",label:(0,c._x)("Between","date","woocommerce")}],input:{component:"Date"}}}});var v=r(55609),p=r(86020),w=r(81595),b=r(74617),f=r(81921),y=r(17844),g=r(39705),h=r(79205),S=function(e){let{isRequesting:o,query:r,filters:l,advancedFilters:a}=e;const m=(0,t.useContext)(y.CurrencyContext),{countries:s,loadingCountries:i}=(0,n.useSelect)((e=>{const{getCountries:o,hasFinishedResolution:r}=e(u.COUNTRIES_STORE_NAME);return{countries:o(),loadingCountries:!r("getCountries")}}));return(0,t.createElement)(g.Z,{endpoint:"customers",getHeadersContent:()=>[{label:(0,c.__)("Name","woocommerce"),key:"name",required:!0,isLeftAligned:!0,isSortable:!0},{label:(0,c.__)("Username","woocommerce"),key:"username",hiddenByDefault:!0},{label:(0,c.__)("Last active","woocommerce"),key:"date_last_active",defaultSort:!0,isSortable:!0},{label:(0,c.__)("Date registered","woocommerce"),key:"date_registered",isSortable:!0},{label:(0,c.__)("Email","woocommerce"),key:"email"},{label:(0,c.__)("Orders","woocommerce"),key:"orders_count",isSortable:!0,isNumeric:!0},{label:(0,c.__)("Total spend","woocommerce"),key:"total_spend",isSortable:!0,isNumeric:!0},{label:(0,c.__)("AOV","woocommerce"),screenReaderLabel:(0,c.__)("Average order value","woocommerce"),key:"avg_order_value",isNumeric:!0},{label:(0,c.__)("Country / Region","woocommerce"),key:"country",isSortable:!0},{label:(0,c.__)("City","woocommerce"),key:"city",hiddenByDefault:!0,isSortable:!0},{label:(0,c.__)("Region","woocommerce"),key:"state",hiddenByDefault:!0,isSortable:!0},{label:(0,c.__)("Postal code","woocommerce"),key:"postcode",hiddenByDefault:!0,isSortable:!0}],getRowsContent:e=>{const o=(0,h.O3)("dateFormat",f.defaultTableDateFormat),{formatAmount:r,formatDecimal:l,getCurrencyConfig:a}=m;return null==e?void 0:e.map((e=>{const{avg_order_value:c,date_last_active:m,date_registered:n,email:u,name:i,user_id:_,orders_count:d,username:f,total_spend:y,postcode:g,city:h,state:S,country:E}=e,C=void 0!==s[A=E]?s[A]:null;var A;const x=_?(0,t.createElement)(p.Link,{href:(0,b.getAdminLink)("user-edit.php?user_id="+_),type:"wp-admin"},i):i,k=m?(0,t.createElement)(p.Date,{date:m,visibleFormat:o}):"—",R=n?(0,t.createElement)(p.Date,{date:n,visibleFormat:o}):"—",N=(0,t.createElement)(t.Fragment,null,(0,t.createElement)(v.Tooltip,{text:C},(0,t.createElement)("span",{"aria-hidden":"true"},E)),(0,t.createElement)("span",{className:"screen-reader-text"},C));return[{display:x,value:i},{display:f,value:f},{display:k,value:m},{display:R,value:n},{display:(0,t.createElement)("a",{href:"mailto:"+u},u),value:u},{display:(0,w.formatValue)(a(),"number",d),value:d},{display:r(y),value:l(y)},{display:r(c),value:l(c)},{display:N,value:E},{display:h,value:h},{display:S,value:S},{display:g,value:g}]}))},getSummary:e=>{const{customers_count:o=0,avg_orders_count:r=0,avg_total_spend:t=0,avg_avg_order_value:l=0}=e,{formatAmount:a,getCurrencyConfig:s}=m,n=s();return[{label:(0,c._n)("customer","customers",o,"woocommerce"),value:(0,w.formatValue)(n,"number",o)},{label:(0,c._n)("Average order","Average orders",r,"woocommerce"),value:(0,w.formatValue)(n,"number",r)},{label:(0,c.__)("Average lifetime spend","woocommerce"),value:a(t)},{label:(0,c.__)("Average order value","woocommerce"),value:a(l)}]},summaryFields:["customers_count","avg_orders_count","avg_total_spend","avg_avg_order_value"],isRequesting:o||i,itemIdField:"id",query:r,labels:{placeholder:(0,c.__)("Search by customer name","woocommerce")},searchBy:"customers",title:(0,c.__)("Customers","woocommerce"),columnPrefsKey:"customers_report_columns",filters:l,advancedFilters:a})},E=r(27410);class C extends t.Component{render(){const{isRequesting:e,query:o,path:r}=this.props,l={orderby:"date_last_active",order:"desc",...o};return(0,t.createElement)(t.Fragment,null,(0,t.createElement)(E.Z,{query:o,path:r,filters:_,showDatePicker:!1,advancedFilters:d,report:"customers"}),(0,t.createElement)(S,{isRequesting:e,query:l,filters:_,advancedFilters:d}))}}C.propTypes={query:a().object.isRequired}},69629:function(e,o,r){r.d(o,{I:function(){return l}});var t=r(65736);function l(e){return[e.country,e.state,e.name||(0,t.__)("TAX","woocommerce"),e.priority].map((e=>e.toString().toUpperCase().trim())).filter(Boolean).join("-")}},68734:function(e,o,r){r.d(o,{FI:function(){return w},V1:function(){return b},YC:function(){return _},hQ:function(){return d},jk:function(){return v},oC:function(){return p},qc:function(){return i},uC:function(){return f}});var t=r(96483),l=r(86989),a=r.n(l),c=r(92819),m=r(10431),s=r(67221),n=r(69629),u=r(79205);function i(e){let o=arguments.length>1&&void 0!==arguments[1]?arguments[1]:c.identity;return function(){let r=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",l=arguments.length>1?arguments[1]:void 0;const c="function"==typeof e?e(l):e,s=(0,m.getIdsFromQuery)(r);if(s.length<1)return Promise.resolve([]);const n={include:s.join(","),per_page:s.length};return a()({path:(0,t.addQueryArgs)(c,n)}).then((e=>e.map(o)))}}i(s.NAMESPACE+"/products/attributes",(e=>({key:e.id,label:e.name})));const _=i(s.NAMESPACE+"/products/categories",(e=>({key:e.id,label:e.name}))),d=i(s.NAMESPACE+"/coupons",(e=>({key:e.id,label:e.code}))),v=i(s.NAMESPACE+"/customers",(e=>({key:e.id,label:e.name}))),p=i(s.NAMESPACE+"/products",(e=>({key:e.id,label:e.name}))),w=i(s.NAMESPACE+"/taxes",(e=>({key:e.id,label:(0,n.I)(e)})));function b(e){let{attributes:o,name:r}=e;const t=(0,u.O3)("variationTitleAttributesSeparator"," - ");if(r&&r.indexOf(t)>-1)return r;const l=(o||[]).map((e=>{let{option:o}=e;return o})).join(", ");return l?r+t+l:r}const f=i((e=>{let{products:o}=e;return o?s.NAMESPACE+`/products/${o}/variations`:s.NAMESPACE+"/variations"}),(e=>({key:e.id,label:b(e)})))}}]);