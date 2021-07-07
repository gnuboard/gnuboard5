	var isIE = false;
	var req01_AJAX;
	var READY_STATE_UNINITIALIZED = 0;
	var READY_STATE_LOADING = 1;
	var READY_STATE_LOADED = 2;
	var READY_STATE_INTERACTIVE = 3;
	var READY_STATE_COMPLETE = 4;
	var PayUrl ="";


	function displayElement( targetObj, targetText, targetColor )
	{
		if ( targetObj.childNodes.length > 0 )
		{
			targetObj.replaceChild( document.createTextNode( targetText ), targetObj.childNodes[ 0 ] );
		} else
		{
			targetObj.appendChild( document.createTextNode( targetText ) );
		}
		targetObj.style.color = targetColor;
	}

	function clearElement( targetObj )
	{
		for ( i = ( targetObj.childNodes.length - 1 ); i >= 0; i-- )
		{
			targetObj.removeChild( targetObj.childNodes[ i ] );
		}
	}

	function initRequest()
	{
		if ( window.XMLHttpRequest )
		{
			return new XMLHttpRequest();
		} else if ( window.ActiveXObject )
		{
			isIE = true;
			return new ActiveXObject( "Microsoft.XMLHTTP" );
		}
	}

	function sendRequest( url )
	{
		req01_AJAX = null;
		req01_AJAX = initRequest();

		if ( req01_AJAX )
		{
			req01_AJAX.onreadystatechange = process_AJAX;
			req01_AJAX.open( "POST", url, true );
			req01_AJAX.send( null );
		}
	}

	function kcp_AJAX()
	{
		var url    = "./order_approval.php";
		var form = document.sm_form;
		var params = "?site_cd=" + form.site_cd.value
				   + "&ordr_idxx=" + form.ordr_idxx.value
				   + "&good_mny=" + form.good_mny.value
				   + "&pay_method=" + form.pay_method.value
				   + "&escw_used=" + form.escw_used.value
				   + "&good_name=" + form.good_name.value
				   + "&Ret_URL=" + form.Ret_URL.value;
		sendRequest( url + params );
	}

	function process_AJAX()
	{
		if ( req01_AJAX.readyState == READY_STATE_COMPLETE )
		{
			if ( req01_AJAX.status == 200 )
			{
				var result = null;

				if ( req01_AJAX.responseText != null )
				{
					var txt = req01_AJAX.responseText.split(",");

					if( txt[0].replace(/^\s*/,'').replace(/\s*$/,'') == '0000' )
					{
						document.getElementById("approval").value = txt[1].replace(/^\s*/,'').replace(/\s*$/,'');
						PayUrl = txt[2].replace(/^\s*/,'').replace(/\s*$/,'');
						//alert("성공적으로 거래가 등록 되었습니다.");
						call_pay_form();
					}
					else
					{
						alert("실패 되었습니다.[" + txt[3].replace(/^\s*/,'').replace(/\s*$/,'') + "]");
					}
				}
			}
			else
			{
				alert( req01_AJAX.responseText );
			}
		}
		else if ( req01_AJAX.readyState == READY_STATE_UNINITIALIZED )
		{
		}
		else if ( req01_AJAX.readyState == READY_STATE_LOADING )
		{
		}
		else if ( req01_AJAX.readyState == READY_STATE_LOADED )
		{
		}
		else if ( req01_AJAX.readyState == READY_STATE_INTERACTIVE )
		{
		}
	}