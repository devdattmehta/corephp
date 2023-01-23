<?php
function getProtocol()
{
  if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
    return 'https://';
  } else {
    return 'http://';
  }
}

function getHost()
{ 
  return $_SERVER ['HTTP_HOST'];
}

function baseUrl()
{
  $pathArr = explode('/',$_SERVER['PHP_SELF']);
  unset($pathArr[count($pathArr)-1]);
  return getProtocol().getHost().implode('/', $pathArr);
}

function showNotificationTo($vars=array())
{
    if (count($vars) > 0) {
?>    <div class="pannel">
        <div class="panel-body">
    <?php 
        foreach ($vars as $notification) {
            $contentHtml = 'Payment has requested from <b>'.$notification['fromName'].'&nbsp;'.money_format('%i', $notification['splitAmt']);
            $contentHtml .= '<input type="button" name="paynow" value="Pay Now" class="form-controll" onclick="doAction(\'paynow\','.$notification['id'].');" />';
            $contentHtml .= '<input type="button" name="reject" value="Reject" class="form-controll" onclick="doAction(\'reject\','.$notification['id'].');"  />';
    ?>
            <div class="col-lg-12">
                <?= $contentHtml; ?>
            </div>
            
    <?php
        } //end foreach ($vars as $user)
    ?>
        </div>
    </div>
<?php 

    } //if (count($vars) > 0) end

} //showNotificationTo() end

function showNotificationFrom($vars=array())
{

    $statusArr = array(0=>'Active',1=>'Inactive',2=>'Paid',3=>'Rejected');
    if (count($vars) > 0) {
?>    
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Details</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
    <?php 
        foreach ($vars as $notification) {
            $contentHtml = 'Payment has requested to <b>'.$notification['emailtonotifiy'].'&nbsp;'.money_format('%i', $notification['splitAmt']);

    ?>
          <tr>
            <td>
                <?= $contentHtml; ?>
            </td>
            <td>
                <?= $statusArr[$notification['Status']]; ?>
            </td>
          </tr>
            
    <?php
        } //end foreach ($vars as $user)
    ?>
          </tbody>
        </table>
<?php 

    } //if (count($vars) > 0) end

} //showNotificationFrom() end

function showLogout()
{

  if(isset($_SESSION['user']['id'])){

    ?>
      <p><a href='logout.php'>Logout</a></p>
    <?php
  }

}
