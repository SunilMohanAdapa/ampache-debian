<?php
/*
 Copyright (c) Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License v2
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/
/**
 * Playlist Box
 * This box is used for actions on the main screen and on a specific playlist page
 * It changes depending on where it is 
 */
?>
<?php show_box_top($playlist->f_type . ' ' . $playlist->name . ' ' . _('Playlist')); ?>
<div id="information_actions">
<ul>
	<li><a href="<?php echo Config::get('web_path'); ?>/playlist.php?action=normalize_tracks&amp;playlist_id=<?php echo $playlist->id; ?>"><?php echo _('Normalize Tracks'); ?></a></li>
        <?php if (Access::check_function('batch_download')) { ?>
                <li><a href="<?php echo Config::get('web_path'); ?>/batch.php?action=playlist&amp;id=<?php echo $playlist->id; ?>">
                        <?php echo _('Batch Download'); ?>
                </a></li>
        <?php } ?>
	<li><?php echo Ajax::text('?action=basket&type=playlist&id=' . $playlist->id,_('Add All'),'play_playlist'); ?></li>
	<li><?php echo Ajax::text('?action=basket&type=playlist_random&id=' . $playlist->id,_('Add Random'),'play_playlist_random'); ?></li>
</ul>
</div>
<?php show_box_bottom(); ?>
<?php 
	$object_ids = $playlist->get_items(); 
	Browse::set_type('playlist_song'); 
	Browse::add_supplemental_object('playlist',$playlist->id); 
	Browse::set_static_content(1);
	Browse::save_objects($object_ids); 
	Browse::show_objects($object_ids); 
?>
