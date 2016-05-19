<form name="quikwallet-form" id="quikwallet-form" action="<?php echo $return_url; ?>" method="POST" >
   <p >Please check your email and mobile number</p>
   <p hidden name="check_message"><strong>Mobile number should be 10 digits</strong></p>
   <p hidden name="check_message_email"><strong>Enter valid email address</strong></p>


   <?php echo $inputs_array; ?>

   <table>
      <tr>
         <td colspan="2" align="center" height="26">

            <input type="submit" name="quikwalletsubmit" id="quikwalletsubmit" value="Pay via QuikWallet" class="free_input" > <?php echo $cancel_url; ?>

            <script type="text/javascript">
              function validateEmail(email) {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
                }

                function validateMobile(mobile) {
               var re =/^\d{10}$/;
               return re.test(mobile);
               }

               jQuery(function(){

                 jQuery("#quikwalletsubmit").click( function() {
                    if (!(validateMobile(jQuery("input[name=phone]").val()))){
                        jQuery("p[name=check_message]").show();
                        return false;
                      }
                   else if (!(validateEmail(jQuery("input[name=quik_email]").val()))){
                     jQuery("p[name=check_message_email]").show();
                     return false;
                   }
                   else{

                   jQuery("body").block({
                     message: "' . __('Do not Refresh or press Back. Redirecting you to QuikWallet to complete the payment.', 'woo_quikwallet') . '",
                       overlayCSS: {
                         background		: "#fff",
                           opacity			: 0.6
                   },
                   css: {
                     padding			: 20,
                       textAlign		: "center",
                       color			: "#555",
                       border			: "3px solid #aaa",
                       backgroundColor	: "#fff",
                       cursor			: "wait",
                       lineHeight		: "32px"
                   }
                   });
                   }
                   } );

                   } );

            </script>
         </td>
      </tr>
   </table>
</form>
