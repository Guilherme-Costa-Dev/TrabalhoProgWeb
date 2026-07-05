
	var Ajax = false;
	function AjaxRequest() {
		Ajax = false;
		if (window.XMLHttpRequest) { // Mozilla, Safari,...
			Ajax = new XMLHttpRequest();
		} else if (window.ActiveXObject) { // IE
			try {
				Ajax = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					Ajax = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {}
			}
		}
	}
	
	function resposta(arq, cam) {
		AjaxRequest();
		if(!Ajax) {
			document.write( '[Erro na Chamada]');
			return;
		}		
		Ajax.onreadystatechange = function () {
		
			if (Ajax.readyState == 4) {
					if (Ajax.status == 200) {						
						document.getElementById(cam).innerHTML = Ajax.responseText;
					}
			}
		}
		
		Ajax.open('GET', arq , true);
		Ajax.send(null);
	}
/*	
	function saidaAjax(c) {
		if (Ajax.readyState == 4) {
				if (Ajax.status == 200) {
					alert('resultado:' + Ajax.responseText);
					document.getElementById(c).innerHTML = Ajax.responseText;
				}
		}
	}
*/	
	function usaAjax(arquivo, camada) {
		resposta(arquivo,camada);
	}