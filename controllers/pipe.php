<?php
/**
 * @package              WP Pipes plugin - PIPES
 * @version              $Id: pipe.php 147 2014-01-25 04:25:54Z tung $
 * @author               wppipes.com
 * @copyright            2014 wppipes.com. All rights reserved.
 * @license              GNU/GPL v3, see LICENSE
 */
defined( 'PIPES_CORE' ) or die( 'Restricted access' );

class PIPESControllerPipe extends Controller {

	public function __construct() {

	}

	public function display() {
		$id    = filter_input( INPUT_GET, 'id' );
		$model = $this->getModel( 'pipe' );
		if ( ! $id ) {
			$temp_id  = $model->create_temp();
			$redirect = add_query_arg( 'id', $temp_id, 'admin.php?page=pipes.pipe' );
			wp_redirect( $redirect );
		} else {
			return;
		}

	}

	public function apply() {
		$this->save();
	}

	function add_temp() {
		$model    = $this->getModel( 'pipes' );
		$return   = $model->create_temp();
		$temp_id  = $return;
		$redirect = 'admin.php?page=pipes.pipe&task=edit&cid[]=' . $temp_id;
		//$this->setRedirect($redirect);
		wp_redirect( $redirect );
	}

	function udfield() {
		$db  = JFactory::getDBO();
		$qry = "show columns from `#__wppipes_items`";
		$db->setQuery( $qry );
		$columns = $db->loadObjectList();
		$cols    = array();
		foreach ( $columns as $column ) {
			$cols[] = $column->Field;
		}
		if ( ! in_array( 'inherit', $cols ) ) {
			$qry = "ALTER TABLE `#__wppipes_items` ADD `inherit` INT( 11 ) NOT NULL DEFAULT '0' AFTER `adapter_params`";
			$db->setQuery( $qry );
			if ( ! $db->query() ) {
				echo '<br>' . $db->getErrorMsg();
			} else {
				echo '<h3>Add inherit field success!</h3>';
			}
		} else {
			echo '<h3>inherit field existing</h3>';
		}
		echo "\n<br /><i><b>File:</b>" . __FILE__ . ' <b>Line:</b>' . __LINE__ . "</i><br />\n"; //exit();
		echo '<pre>';
		print_r( $cols );
		echo '</pre>';
		exit();
		//exit("<h4>Stop ".__LINE__."</h4>");


		# 1.1.1 ADD `inherit` field to #__wppipes_items table
		//$qry = "ALTER TABLE `#__wppipes_items` ADD `inherit` INT( 11 ) NOT NULL DEFAULT '0' AFTER `adapter_params`";

		# 1.2.1 ADD `inherit` field to #__wppipes_pipes table
		//$qry = ""ALTER TABLE `#__wppipes_pipes` ADD `inherit` INT( 11 ) NOT NULL DEFAULT '0' AFTER `item_id`";
		/*
				$db->setQuery($qry);
				if (!$db->query()) {
					echo '<br>'.$db->getErrorMsg();
				} else {
					//echo "<br />Created #__wppipes_items success table";
				}
		*/
	}

	function qadd() {
		$page = 'quickadd';
		require_once OBGRAB_SITE . 'pages' . DS . $page . DS . 'index.php';
		$pageCl  = 'ogb_page_' . $page;
		$control = new $pageCl;
		$control->display();
	}

	function qedit() {
		$page = 'quickedit';
		require_once OBGRAB_SITE . 'pages' . DS . $page . DS . 'index.php';
		$pageCl  = 'ogb_page_' . $page;
		$control = new $pageCl;
		$control->display();
	}

	function inhe() {
		$page = 'inherit';
		require_once OBGRAB_SITE . 'pages' . DS . $page . DS . 'index.php';
		$pageCl  = 'ogb_page_' . $page;
		$control = new $pageCl;
		$control->display();
	}

	function copy() {
		$id     = JRequest::getInt( 'id', 0 );
		$mod    = $this->getModel( 'pipes' );
		$cop_id = $mod->copyItem( $id );
		global $mainframe, $option;
		$mainframe->redirect( "index.php?option={$option}&controller=items&task=edit&cid[]={$cop_id}" );
	}

	function cfdf() {
		echo '<pre>';
		print_r( $_REQUEST );
		exit();
	}

	function getUrls( $urls, $id = 0 ) {
		$urls  = explode( "\nhttp", $urls );
		$srcs  = array();
		$pipes = array();
		for ( $i = 0; $i < count( $urls ); $i ++ ) {
			$url  = trim( $urls[$i] );
			$info = "[ ";
			if ( $i > 0 ) {
				$url = 'http' . $url;
				$info .= "--- {$i}";
			} else {
				$info .= "{$id}";
			}
			$a      = str_replace( 'http://www.', 'http://', $url ) . " [{$id}]";
			$srcs[] = str_replace( 'http://', '', $a );

			$info .= " ][ <a href=\"{$url}\" target=\"_blank\">{$url}</a> ]";
			$pipes[] = $info;
		}
		$res = array( 'pipes' => $pipes, 'srcs' => $srcs );

		return $res;
	}

	function sinfo() {
		//echo "\n\n<br /><i><b>File:</b>".__FILE__.' <b>Line:</b>'.__LINE__."</i><br />\n\n";		
		$db     = JFactory::getDBO();
		$select = '`id`,`name`,`engine_params`';
		if ( isset( $_GET['x'] ) ) {
			$select = '*';
		}
		$qry = "SELECT {$select} FROM `#__wppipes_items` WHERE `engine` = 'rssreader' ORDER BY id LIMIT 1000";
		$db->setQuery( $qry );
		$rows = $db->LoadObjectList();
		$res  = array();
		$srcs = array();
		for ( $i = 0; $i < count( $rows ); $i ++ ) {
			$rows[$i]->engine_params = json_decode( $rows[$i]->engine_params );
			$row                     = $rows[$i];
			$info                    = $this->getUrls( $row->engine_params->feed_url, $row->id );
			$res                     = array_merge( $res, $info['pipes'] );
			$srcs                    = array_merge( $srcs, $info['srcs'] );

		}
		if ( isset( $_GET['v2'] ) ) {
			echo "\n\n<br /><i><b>File:</b>" . __FILE__ . ' <b>Line:</b>' . __LINE__ . "</i><br />\n\n";
			asort( $srcs );
			echo "<ol>\n";
			foreach ( $srcs as $src ) {
				echo "\n<li>{$src}</li>";
			}
			echo "\n</ol><hr/>\n";
		}
		echo "<ol>\n<li>\n" . implode( "\n</li>\n<li>\n", $res ) . "</li>\n</ol>";
		exit();
	}

	//=== POST ===
	function post() {
		require_once PIPES_PATH . DS . 'post.php';
		ogbPost::Post();
	}

	function gengin() {
		require_once PIPES_PATH . DS . 'post.php';
		ogbPost::getEngin();
	}

	function asave() {
		require_once PIPES_PATH . DS . 'post.php';
		ogbPost::saveAdapter();
	}

	//=== END POST ===

	function viewlog() {
		require_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'cronlog.php';
	}

	function getioaddon() {
		$type = filter_input( INPUT_GET, 'type' );
		$name = filter_input( INPUT_GET, 'name' );
		$id   = filter_input( INPUT_GET, 'id' );

		$mod    = $this->getModel( 'pipe' );
		$params = $mod->getAddonParam( $type, $name, $id, false );
		$res    = $mod->getIOaddon( $type, $name, $params );
		$txt    = json_encode( $res );
		echo $txt;
		exit();
	}

	function remove_pipe() {
		$pid  = filter_input( INPUT_GET, 'pid', FILTER_VALIDATE_INT );
		$itid = filter_input( INPUT_GET, 'itid', FILTER_VALIDATE_INT );
		$mod  = $this->getModel( 'pipe' );
		$msg  = $mod->removePipe( $pid, $itid );
		echo $msg;
		exit();
	}

	function addprocess() {
		$code     = filter_input( INPUT_GET, 'code' );
		$id       = filter_input( INPUT_GET, 'id' );
		$ordering = filter_input( INPUT_GET, 'order' );

		$mod  = $this->getModel( 'pipe' );
		$res  = $mod->addProcess( $code, $id, $ordering );
		$json = json_encode( $res );
		echo $json;
		exit();
	}

	function gaparam() {
		$type = filter_input( INPUT_GET, 'type' );
		$name = filter_input( INPUT_GET, 'name' );
		$id   = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );

		$mod = $this->getModel( 'pipe' );
		$txt = $mod->getAddonParam( $type, $name, $id );
		echo $txt;
		exit();
	}

	function save() {
		/*echo "\n\n<br /><i><b>File:</b>".__FILE__.' <b>Line:</b>'.__LINE__."</i><br />\n\n";
		echo '<pre>';
		print_r($_POST);
		echo '</pre>';exit();*/

		global $mainframe, $option;
		$mod = $this->getModel( 'pipe' );
		$res = $mod->save();
		$msg = $res->msg;
		PIPES::add_message( $msg );

		$task = filter_input( INPUT_POST, 'task' );
		//$apply	= $task=='apply'?'&task=edit&id[]='.$res->id:'';
		$url = admin_url() . 'admin.php?page=' . PIPES::$__page_prefix . '.pipe&id=' . $res->id;
		header( 'Location: ' . $url );
		exit();
//		$mainframe->enqueueMessage($msg, $res->typemsg);
//		$mainframe->redirect("index.php?option={$option}&controller=items".$apply);
	}

	function cancel() {
		$mode = $this->getModel( 'pipe' );
		$mode->remove_if_no_ip();
		$url = admin_url() . 'admin.php?page=' . PIPES::$__page_prefix . '.pipes';
		header( 'Location: ' . $url );
//		$mainframe->redirect( "index.php?option={$option}&controller=items");
	}

	function remove() {
		global $mainframe, $option;
		$cid = JRequest::getVar( 'cid', array(), '', 'array' );
		JArrayHelper::toInteger( $cid );
		$row = JTable::getInstance( 'Pipes', 'wppipesTable' );
		$msg = '';
		foreach ( $cid as $id ) {
			$row->load( $id );
			if ( $row->delete() ) {
				$msg .= $row->getError();
			} else {
				$msg .= "Delete success [{$id}]";
			}
			$msg .= '<br />';
		}
		//echo $msg;exit();
		$mainframe->enqueueMessage( $msg );
		$mainframe->redirect( "index.php?option={$option}&controller=items" );
	}

	function itemspublish() {
		$cid = JRequest::getVar( 'cid', array(), '', 'array' );
		$this->setPublish( '1', $cid );
	}

	function publish() {
		$cid = JRequest::getVar( 'cid', array(), '', 'array' );
		$this->setPublish( '1', $cid );
	}

	function unpublish() {
		$cid = JRequest::getVar( 'cid', array(), '', 'array' );
		$this->setPublish( '0', $cid );
	}

	function itemsunpublish() {
		$cid = JRequest::getVar( 'cid', array(), '', 'array' );
		$this->setPublish( '0', $cid );
	}

	function savenote() {
		global $mainframe, $option;
		$new_note = empty( $_REQUEST['new_note'] ) ? '' : $_REQUEST['new_note'];
		$id       = empty( $_REQUEST['id'] ) ? 0 : $_REQUEST['id'];

		$mod  = $this->getModel( 'pipe' );
		$res  = $mod->savenote( $id, $new_note );
		$json = json_encode( $res );
		echo $json;
		exit();
	}

	function save_b4_post() {
		$mod = $this->getModel( 'pipe' );
		$res = $mod->save_b4_post();
		echo json_encode( $res );
		exit();
	}

	function iwant() {
		$config     = JFactory::getConfig();
		$cur_url    = urldecode( JRequest::getVar( 'cur_url' ) );
		$from_name  = $config->get( 'fromname' );
		$from_email = $config->get( 'mailfrom' );
		$to_email   = 'iwant@wppipes.com';
		$mailer     = JFactory::getMailer();
		$mailer->isHTML( true );
		$message = JRequest::getVar( 'mess' );
		$mes_arr = explode( ' ', $message );
		if ( count( $mes_arr ) > 6 ) {
			$mes_sub = '';
			for ( $i = 0; $i <= 5; $i ++ ) {
				$mes_sub .= $mes_arr[$i] . ' ';
			}
		} else {
			$mes_sub = $message;
		}
		$subject  = $mes_sub . '...';
		$mailBody = 'Dear obTeam,<br/><br/>';
		$mailBody .= '<p>' . $message . '</p>';
		$mailBody .= '<p>From link: ' . $cur_url . '</p>';
		$return = $mailer->sendMail( $from_email, $from_name, $to_email, $subject, $mailBody );
		if ( $return ) {
			echo JText::_( 'OBGRABBER_YOUR_MESSAGE_WAS_SENT' );
		} else {
			echo $return;
		}
		exit();
	}
}