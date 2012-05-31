<?php
/* vim:set tabstop=8 softtabstop=8 shiftwidth=8 noexpandtab: */
/**
 * Show Manage Democratic
 *
 *
 * LICENSE: GNU General Public License, version 2 (GPLv2)
 * Copyright (c) 2001 - 2011 Ampache.org All Rights Reserved
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License v2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @package	Ampache
 * @copyright	2001 - 2011 Ampache.org
 * @license	http://opensource.org/licenses/gpl-2.0 GPLv2
 * @link	http://www.ampache.org/
 */

show_box_top(T_('Manage Democratic Playlists'));  ?>
<table class="tabledata" cellpadding="0" cellspacing="0">
<colgroup>
	<col id="col_number" />
	<col id="col_base_playlist" />
	<col id="col_vote_count" />
	<col id="col_cooldown" />
	<col id="col_level" />
	<col id="col_default" />
	<col id="col_action" />
</colgroup>
<tr class="th-top">
	<th class="cel_number"><?php echo T_('Playlist'); ?></th>
	<th class="cel_base_playlist"><?php echo T_('Base Playlist'); ?></th>
	<th class="cel_cooldown"><?php echo T_('Cooldown'); ?></th>
	<th class="cel_level"><?php echo T_('Level'); ?></th>
	<th class="cel_default"><?php echo T_('Default'); ?></th>
	<th class="cel_vote_count"><?php echo T_('Songs'); ?></th>
	<th class="cel_action"><?php echo T_('Action'); ?></th>
</tr>
<?php
	foreach ($playlists as $democratic_id) {
		$democratic = new Democratic($democratic_id);
		$democratic->format();
		$playlist = new Playlist($democratic->base_playlist);
		$playlist->format();
?>
<tr class="<?php echo flip_class(); ?>">
	<td><?php echo scrub_out($democratic->name); ?></td>
	<td><?php echo $playlist->f_link; ?></td>
	<td><?php echo $democratic->f_cooldown; ?></td>
	<td><?php echo $democratic->f_level; ?></td>
	<td><?php echo $democratic->f_primary; ?></td>
	<td><?php echo $democratic->count_items(); ?></td>
	<td>
	<?php echo Ajax::button('?page=democratic&action=send_playlist&democratic_id=' . $democratic->id,'all', T_('Play'),'play_democratic'); ?>
	<a href="<?php echo Config::get('web_path'); ?>/democratic.php?action=delete&amp;democratic_id=<?php echo scrub_out($democratic->id); ?>"><?php echo get_user_icon('delete', T_('Delete')); ?></a>
	</td>
</tr>
<?php } if (!count($playlists)) { ?>
<tr>
	<td colspan="7"><span class="fatalerror"><?php echo T_('Not Enough Data'); ?></span></td>
</tr>
<?php } ?>
</table>
<br />
<div>
<a class="button" href="<?php echo Config::get('web_path'); ?>/democratic.php?action=show_create"><?php echo T_('Create New Playlist'); ?></a>
</div>
<?php show_box_bottom(); ?>
