<?php
/*

 Copyright (c) 2001 - 2007 Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; version 2
 of the License.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/
?>
<?php show_box_top($working_user->fullname . ' ' . _('IP History')); ?>
<div>
<?php if (isset($_REQUEST['all'])){ ?>
	<a class="button" href="<?php echo Config::get('web_path')?>/admin/users.php?action=show_ip_history&user_id=<?php echo $working_user->id?>"><?php echo _('Show Unique'); ?>...</a>
<?php }else{ ?>
	<a class="button" href="<?php echo Config::get('web_path')?>/admin/users.php?action=show_ip_history&user_id=<?php echo $working_user->id?>&all"><?php echo _('Show All'); ?>...</a>
<?php }?>
</div>
<table class="tabledata" cellpadding="0" cellspacing="0">
<colgroup>
  <col id="col_date" />
  <col id="col_ipaddress" />
</colgroup>
<tr class="th-top">
  <th class="cel_date"><?php echo _('Date'); ?></th>
 	<th class="cel_ipaddress"><?php echo _('IP Address'); ?></th>
</tr>
<?php foreach ($history as $data) { ?>
<tr class="<?php echo flip_class(); ?>">
	<td class="cel_date">
		<?php echo date("d/m/Y H\hi",$data['date']); ?>
	</td>
	<td class="cel_ipaddress">
		<?php echo long2ip($data['ip']); ?>
	</td>
</tr>
<?php } ?>
<tr class="th-bottom">
  <th class="cel_date"><?php echo _('Date'); ?></th>
 	<th class="cel_ipaddress"><?php echo _('IP Address'); ?></th>
</tr>

</table>
<?php show_box_bottom(); ?>
