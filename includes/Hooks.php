<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\URNames;

use MediaWiki\Linker\UserLinkRenderer;
use MediaWiki\MediaWikiServices;

class Hooks {
	public static function onMediaWikiServices( MediaWikiServices $services ): void {
		$services->redefineService(
			'UserLinkRenderer',
			static function ( MediaWikiServices $services ): UserLinkRenderer {
				return new DecoratedUserLinkRenderer(
					$services->getHookContainer(),
					$services->getTempUserConfig(),
					$services->getSpecialPageFactory(),
					$services->getLinkRenderer(),
					$services->getTempUserDetailsLookup(),
					$services->getUserIdentityLookup(),
					$services->getUserNameUtils(),
					$services->getUserFactory(),
					$services->getMainWANObjectCache(),
					$services->getMainConfig()
				);
			}
		);
	}
}
