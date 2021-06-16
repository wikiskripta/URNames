<?php

/**
 * All hooked functions used by URNames
 * @ingroup Extensions
 * @author Josef Martiňák
 * @license MIT
 * @file
 */

class URNamesHooks {

	/**
	 * @var string $pagename: page name, we want to add a real name to
	 * @example: 'Listusers', 'Activeusers', 'Blocklist', 'RecentChanges', 'history'
	 */
	private $pagename;

	/**
	 * @var object $user: instance of User
	 */
	private $user;

	/**
	 * @var object $out: instance of OutputPage
	 */
	private $out;


	/**
	 * Constructor - init the vars
	 */
	public function __construct( $pagename, $user, $out ) {
		$this->pagename = $pagename;
		$this->user = $user;
		$this->out = $out;
	}


	/**
	 * Add real name to output code
	 */
	public function replace() {
		/*$pattern = '/title=\"([^\"]*)\"( class=\"[^\"]*\")?>([^<]*)<\/a>/';*/
		$pattern = '/<bdi>([^<]*)<\/bdi>/';
		$callback = preg_replace_callback( $pattern, array( $this, 'defaultReplace' ),
			$this->out->mBodytext );
		return $callback;
	}


	/**
	 * Replace code parts - default method
	 * @param array $matches: found occurances of searched string
	 * @return code part with real name
	 */
	public function defaultReplace( $matches ) {
		//$output = "title=\"$matches[1]\"$matches[2]>$matches[3]</a> ";
		//$output .= '(' . $this->user->whoIsReal( $this->user->idFromName( $matches[3] ) ) . ')';
		$output = $matches[0] . ' (' . $this->user->whoIsReal( $this->user->idFromName( $matches[1] ) ) . ')';
		$output = preg_replace( '/ \(\)/', '', $output );
		return $output;
	}

	
	/**
	 * Adds real user names to specific special page or history page
	 * @param object $out: instance of OutputPage
	 * @param object $skin: instance of Skin, unused
	 */
	public static function replaceUserNames( &$out, &$skin ) {

		$user = $out->getUser();
		$query = $out->getRequest()->getQueryValues();
			if( !$user->isLoggedIn() ) {
			// user is not logged - no action
			return true;
		}

		$title = $out->getTitle();
		$pagename = '';
	
		if( $title->isSpecialPage() ) {
			$spList = ['Recentchanges', 'Activeusers', 'BlockList', 'Listusers'];
			foreach($spList as $sp) {
			    if($title->getBaseTitle() == SpecialPage::getTitleFor($sp)->getBaseTitle()) {
				$pagename = $sp;
				break;
			    }
			}
		}
		elseif( isset( $query['action'] ) && $query['action'] == 'history' ) {
			$pagename = 'history';
		}
		if( in_array( $pagename, array( 'Recentchanges', 'Activeusers', 'BlockList', 'Listusers', 'history' ) ) ) {
			$urnames = new URNamesHooks( $pagename, $user, $out );
			$out->mBodytext = $urnames->replace();
		}

		return true;
	}

}