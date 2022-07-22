



			<div class=":hidden_if_fail">
				<div class="textLarger">
					:localize:banxa_info_top:
				</div>				
				
				<div class="verticalSpacer"></div>

				<div>
					<div class="">:localize:banxa_info_amount:</div>
					<input id="bcAmount" type="text" disabled value=":bc_amount"/>
					<div data-id="bcAmount" class="beige inline bold copyButton pointer">:localize:bo_main_copy_button:</div>
				</div>

				<div class="verticalSpacer"></div>

				<div>
					<div class="">:localize:banxa_info_adress:</div>
					<input id="bcAddress" type="text" disabled value=":bc_address"/>
					<div data-id="bcAddress" class="beige inline bold copyButton pointer">:localize:bo_main_copy_button:</div>
				</div>

				<div class="verticalSpacer"></div>
				<div class="verticalSpacer"></div>

				<div class="textNormal">
					:localize:banxa_info_bottom:
				</div>	
			</div>
			
			<div class=":hidden_if_success">
				<div class="textLarger red_bitcoin_failed">
					:localize:payment_bitcoin_payment_failed:
				</div>
			</div>

			<script>
				$('.copyButton').on('click', function(event) {
					var copyInput = $(this).data('id');

					try {
						var copyText = document.getElementById(copyInput);
						console.log(copyText.getAttribute("disabled"));
						copyText.disabled = false;
						copyText.select();
						copyText.setSelectionRange(0, 99999); /*For mobile devices*/
						
						document.execCommand("copy");
						copyText.disabled = true;
					} catch (err) {
						console.log('Oops, unable to copy');
					}
				});

				const popupCenter = ({url, title, w, h}) => {
					// Fixes dual-screen position                             Most browsers      Firefox
					const dualScreenLeft = window.screenLeft !==  undefined ? window.screenLeft : window.screenX;
					const dualScreenTop = window.screenTop !==  undefined   ? window.screenTop  : window.screenY;

					const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
					const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

					const systemZoom = width / window.screen.availWidth;
					const left = (width / 2 - w) / 2 / systemZoom + dualScreenLeft;
					const top = (height - h) / 2 / systemZoom + dualScreenTop + 80;
					const newWindow = window.open(url, title, 
					`
					scrollbars=yes,
					width=${w / systemZoom}, 
					height=${h / systemZoom}, 
					top=${top}, 
					left=${left}
					`
					);

					if (window.focus) newWindow.focus();
				}

				if (:bc_success == true)
				{
					popupCenter({url: 'https://checkout.banxa.com', title: 'Banxa', w: 660, h: 780});  
				}
			</script>
