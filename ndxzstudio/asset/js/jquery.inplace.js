/*
+-----------------------------------------------------------------------+
| Copyright (c) 2007 David Hauenstein			                |
| All rights reserved.                                                  |
|                                                                       |
| Redistribution and use in source and binary forms, with or without    |
| modification, are permitted provided that the following conditions    |
| are met:                                                              |
|                                                                       |
| o Redistributions of source code must retain the above copyright      |
|   notice, this list of conditions and the following disclaimer.       |
| o Redistributions in binary form must reproduce the above copyright   |
|   notice, this list of conditions and the following disclaimer in the |
|   documentation and/or other materials provided with the distribution.|
|                                                                       |
| THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
| "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
| LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
| A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
| OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
| SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
| LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
| DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
| THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
| (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
| OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
|                                                                       |
+-----------------------------------------------------------------------+
*/

/* $Id: jquery.inplace.js,v 0.9.9 2007/03/06 18:00:00 tuupola Exp $ */

/**
  * Created by: David Hauenstein
  * http://www.davehauenstein.com/blog/
  *
  * Repacked by Vaska to add maxlenght deftault of 35 characters 2007/03/22
  *
*/

eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('8.1x.1y=9(O){4 2={S:"",h:"",G:"l",1b:"",19:"25",1a:"10",1k:"#1t",w:"1s",1e:"1l...",p:"",E:"(1L 1H D 1I l)",13:"1J 15 c",1g:I,M:"M",T:"T",1K:"1z",b:"b",12:\'<K H="1f" P="1o" c="1B"/>\',16:\'<K H="1f" P="1n" c="1A"/>\',X:I,F:I,A:9(r){U("1d D L c: "+r.1C||\'1D 1h\')}};5(O){8.1G(2,O)}5(2.p!=""){4 11=15 1E();11.1c=2.p}18.14.t=9(){o 6.m(/^\\s+/,\'\').m(/\\s+$/,\'\')};18.14.q=9(){o 6.m(/&/g,"&1F;").m(/</g,"&1v;").m(/>/g,"&1r;").m(/"/g,"&1u;")};o 6.1w(9(){5(8(6).3()=="")8(6).3(2.E);4 e=f;4 7=8(6);4 d=0;8(6).1q(9(){8(6).C("y",2.1k)}).1S(9(){8(6).C("y",2.w)}).R(9(){d++;5(!e){e=27;4 b=8(6).3();4 1m=2.12+\' \'+2.16;5(b==2.E)8(6).3(\'\');5(2.G=="N"){4 n=\'<N Y="J" 26="\'+2.1a+\'" 24="\'+2.19+\'">\'+8(6).l().t().q()+\'</N>\'}j 5(2.G=="l"){4 n=\'<K H="l" Y="J" 22="\'+2.23+\'" c="\'+8(6).l().t().q()+\'" />\'}j 5(2.G=="Q"){4 W=2.1b.17(\',\');4 n=\'<Q Y="J"><B c="">\'+2.13+\'</B>\';29(4 i=0;i<W.2a;i++){4 z=W[i].17(\':\');4 V=z[1]||z[0];4 x=V==b?\'x="x" \':\'\';n+=\'<B \'+x+\'c="\'+V.t().q()+\'">\'+z[0].t().q()+\'</B>\'}n+=\'</Q>\'}8(6).3(\'<v P="2d" 2b="28: 1M; 21: 0; 1R: 0;">\'+n+\' \'+1m+\'</v>\')}5(d==1){7.u("v").u(".1n").R(9(){e=f;d=0;7.C("y",2.w);7.3(b);o f});7.u("v").u(".1o").R(9(){7.C("y",2.w);4 k=8(6).1Q().u(0).1P();5(2.p!=""){4 Z=\'<1N 1c="\'+2.p+\'" 1O="1l..." />\'}j{4 Z=2.1e}7.3(Z);5(2.h!=""){2.h="&"+2.h}5(2.X){3=2.X(7.1i("1j"),k,b,2.h);e=f;d=0;5(3){7.3(3||k)}j{U("1d D L c: "+k);7.3(b)}}j 5(2.1g&&k==""){e=f;d=0;7.3(b);U("1h: 1T 1U 1Z a c D L 6 1Y")}j{8.1X({S:2.S,H:"1V",1W:2.T+\'=\'+k+\'&\'+2.M+\'=\'+7.1i("1j")+2.h+\'&\'+2.b+\'=\'+b,20:"3",2c:9(r){e=f;d=0},F:9(3){4 1p=3||2.E;7.3(1p);5(2.F)2.F(3,7)},A:9(r){7.3(b);5(2.A)2.A(r,7)}})}o f})}})})};',62,138,'||settings|html|var|if|this|original_element|jQuery|function||original_html|value|click_count|editing|false||params||else|new_html|text|replace|use_field_type|return|saving_image|escape_html|request||trim|children|form|bg_out|selected|background|optionsValuesArray|error|option|css|to|default_text|success|field_type|type|null|inplace_value|input|save|element_id|textarea|options|class|select|click|url|update_value|alert|use_value|optionsArray|callback|name|saving_message||loading_image|save_button|select_text|prototype|new|cancel_button|split|String|textarea_cols|textarea_rows|select_options|src|Failed|saving_text|submit|value_required|Error|attr|id|bg_over|Saving|buttons_code|inplace_cancel|inplace_save|new_text|mouseover|gt|transparent|ffc|quot|lt|each|fn|editInPlace|35|Cancel|Save|responseText|Unspecified|Image|amp|extend|here|add|Choose|max_lenth|Click|inline|img|alt|val|parent|padding|mouseout|You|must|POST|data|ajax|field|enter|dataType|margin|maxlength|max_length|cols||rows|true|display|for|length|style|complete|inplace_form'.split('|'),0,{}))