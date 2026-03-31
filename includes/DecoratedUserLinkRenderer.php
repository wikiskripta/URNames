<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\URNames;

use IContextSource;
use MediaWiki\Config\Config;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\UserLinkRenderer;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\Title\Title;
use MediaWiki\User\TempUser\TempUserConfig;
use MediaWiki\User\TempUser\TempUserDetailsLookup;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserIdentityLookup;
use MediaWiki\User\UserNameUtils;
use Wikimedia\ObjectCache\WANObjectCache;

class DecoratedUserLinkRenderer extends UserLinkRenderer {
	private UserFactory $userFactory;
	private WANObjectCache $cache;
	private Config $config;

	public function __construct(
		HookContainer $hookContainer,
		TempUserConfig $tempUserConfig,
		SpecialPageFactory $specialPageFactory,
		LinkRenderer $linkRenderer,
		TempUserDetailsLookup $tempUserDetailsLookup,
		UserIdentityLookup $userIdentityLookup,
		UserNameUtils $userNameUtils,
		UserFactory $userFactory,
		WANObjectCache $cache,
		Config $config
	) {
		parent::__construct(
			$hookContainer,
			$tempUserConfig,
			$specialPageFactory,
			$linkRenderer,
			$tempUserDetailsLookup,
			$userIdentityLookup,
			$userNameUtils
		);

		$this->userFactory = $userFactory;
		$this->cache = $cache;
		$this->config = $config;
	}

	public function userLink(
		UserIdentity $targetUser,
		IContextSource $context,
		?string $altUserName = null,
		array $attributes = []
	): string {
		$link = parent::userLink( $targetUser, $context, $altUserName, $attributes );

		if ( !$this->shouldDecorate( $targetUser, $context, $altUserName ) ) {
			return $link;
		}

		$realName = $this->getRealName( $targetUser );
		if ( $realName === '' ) {
			return $link;
		}

		return $link . ' <span class="urnames-realname">(' . htmlspecialchars( $realName ) . ')</span>';
	}

	private function shouldDecorate(
		UserIdentity $targetUser,
		IContextSource $context,
		?string $altUserName
	): bool {
		$viewer = $context->getUser();
		if ( !$viewer || !$viewer->isRegistered() ) {
			return false;
		}

		if ( $targetUser->getId() <= 0 ) {
			return false;
		}

		$userName = $targetUser->getName();
		if ( $userName === '' ) {
			return false;
		}

		if ( $altUserName !== null && trim( $altUserName ) !== '' && trim( $altUserName ) !== $userName ) {
			return false;
		}

		$title = $context->getTitle();
		if ( !$this->isSupportedPage( $title, $context ) ) {
			return false;
		}

		return true;
	}

	private function isSupportedPage( ?Title $title, IContextSource $context ): bool {
		if ( !$title ) {
			return false;
		}

		$supported = $this->config->get( 'URNamesPages' );

		if ( $title->isSpecialPage() ) {
			foreach ( $supported as $specialName ) {
				if ( $specialName === 'history' ) {
					continue;
				}
				$specialTitle = SpecialPage::getTitleFor( $specialName );
				if ( $specialTitle && $title->getBaseTitle()->equals( $specialTitle->getBaseTitle() ) ) {
					return true;
				}
			}
		}

		return in_array( 'history', $supported, true )
			&& $context->getRequest()->getVal( 'action' ) === 'history';
	}

	private function getRealName( UserIdentity $targetUser ): string {
		$userId = (int)$targetUser->getId();
		$cacheKey = $this->cache->makeKey( 'urnames', 'realname', 'v3', (string)$userId );
		$ttl = (int)$this->config->get( 'URNamesCacheTTL' );

		$value = $this->cache->getWithSetCallback(
			$cacheKey,
			$ttl,
			function () use ( $userId ): string {
				$user = $this->userFactory->newFromId( $userId );
				if ( !$user || !$user->isRegistered() ) {
					return '';
				}

				$realName = trim( (string)$user->getRealName() );
				if ( $realName === '' || $realName === $user->getName() ) {
					return '';
				}

				return $realName;
			}
		);

		return is_string( $value ) ? $value : '';
	}
}
