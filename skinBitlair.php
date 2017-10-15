<?php
/**
 * Cologne Blue: A nicer-looking alternative to Standard.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @todo document
 * @file
 * @ingroup Skins
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( -1 );
}

/**
 * @todo document
 * @ingroup Skins
 */
class SkinBitLair extends SkinTemplate {
	public $skinname = 'bitlair';
	public $template = 'BitlairTemplate';
	public $stylename = 'Bitlair';
	/**
	 * @param OutputPage $out
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );
    
    $out->addModuleStyles( array(
//			'mediawiki.skinning.interface',
			'skins.bitlair.styles' 
		) );
    
    
		// TODO: Migrate all of these
		$out->addStyle( $this->stylename . '/IE60Fixes.css', 'screen', 'IE 6' );
		$out->addStyle( $this->stylename . '/IE70Fixes.css', 'screen', 'IE 7' );
	}

	/**
	 * Initializes output page and sets up skin-specific parameters
	 * @param OutputPage $out Object to initialize
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

			$out->addMeta( 'viewport', 'initial-scale = 1.0, maximum-scale = 1.0, user-scalable = no, width = device-width' );
			$out->addModuleStyles( 'skins.bitlair.styles.responsive' );


		$out->addModules( 'skins.bitlair.js' );
	}
  
  function addToSidebarPlain( &$bar, $text ) {
		$lines = explode( "\n", $text );

		$heading = ''; $lastitem = ['sub'=>[]];

		foreach ( $lines as $line ) {
			if ( strpos( $line, '*' ) !== 0 ) {
				continue;
			}
			$line = rtrim( $line, "\r" ); // for Windows compat

			if ( strpos( $line, '**' ) !== 0 ) {
				$heading = trim( $line, '* ' );
				if ( !array_key_exists( $heading, $bar ) ) {
					$bar[$heading] = [];
				}
			} else {
        $isSub = strpos( $line, '***' ) === 0;
       
				$line = trim( $line, '* ' );

				if ( strpos( $line, '|' ) !== false ) { // sanity check
					$line = MessageCache::singleton()->transform( $line, false, null, $this->getTitle() );
					$line = array_map( 'trim', explode( '|', $line, 2 ) );
					if ( count( $line ) !== 2 ) {
						// Second sanity check, could be hit by people doing
						// funky stuff with parserfuncs... (bug 33321)
						continue;
					}

					$extraAttribs = [];

					$msgLink = $this->msg( $line[0] )->inContentLanguage();
					if ( $msgLink->exists() ) {
						$link = $msgLink->text();
						if ( $link == '-' ) {
							continue;
						}
					} else {
						$link = $line[0];
					}
					$msgText = $this->msg( $line[1] );
					if ( $msgText->exists() ) {
						$text = $msgText->text();
					} else {
						$text = $line[1];
					}

					if ( preg_match( '/^(?i:' . wfUrlProtocols() . ')/', $link ) ) {
						$href = $link;

						// Parser::getExternalLinkAttribs won't work here because of the Namespace things
						global $wgNoFollowLinks, $wgNoFollowDomainExceptions;
						if ( $wgNoFollowLinks && !wfMatchesDomainList( $href, $wgNoFollowDomainExceptions ) ) {
							$extraAttribs['rel'] = 'nofollow';
						}

						global $wgExternalLinkTarget;
						if ( $wgExternalLinkTarget ) {
							$extraAttribs['target'] = $wgExternalLinkTarget;
						}
					} else {
						$title = Title::newFromText( $link );

						if ( $title ) {
							$title = $title->fixSpecialName();
							$href = $title->getLinkURL();
						} else {
							$href = 'INVALID-TITLE';
						}
					}
          $item = array_merge( [
						'text' => $text,
						'href' => $href,
						'id' => 'n-' . Sanitizer::escapeId( strtr( $line[1], ' ', '-' ), 'noninitial' ),
						'active' => false,
            'sub' => []
					], $extraAttribs );
          
          if ($isSub) {
            $bar[$heading][$lastitem-1] ['sub'][] = $item;
          } else {
  					$bar[$heading][] = $item;
            $lastitem = count($bar[$heading]);
          }
				} else {
					continue;
				}
			}
		}
		return $bar;
	}
  
  
	/**
	 * Override langlink formatting behavior not to uppercase the language names.
	 * See otherLanguages() in CologneBlueTemplate.
	 * @param string $name
	 * @return string
	 */
	function formatLanguageName( $name ) {
		return $name;
	}
}

class BitLairTemplate extends BaseTemplate {
  
  function getNav(){
    $nav = $this->data['content_navigation'];

		$xmlID = '';
		foreach ( $nav as $section => $links ) {
			foreach ( $links as $key => $link ) {
				if ( $section == 'views' && !( isset( $link['primary'] ) && $link['primary'] ) ) {
					$link['class'] = rtrim( 'collapsible ' . $link['class'], ' ' );
				}

				$xmlID = isset( $link['id'] ) ? $link['id'] : 'ca-' . $xmlID;
				$nav[$section][$key]['attributes'] =
					' id="' . Sanitizer::escapeId( $xmlID ) . '"';
				if ( $link['class'] ) {
					$nav[$section][$key]['class'] =	' class="' . (strpos($link['class'],'selected')===false?'':'active ') .$link['class'] . '"';//?"active":""
					//unset( $nav[$section][$key]['class'] );
				}
				if ( isset( $link['tooltiponly'] ) && $link['tooltiponly'] ) {
					$nav[$section][$key]['key'] =
						Linker::tooltip( $xmlID );
				} else {
					$nav[$section][$key]['key'] =
						Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( $xmlID ) );
				}
			}
		}    
 //   echo "<pre>";
  //  print_r($this->data);
 //   echo "</pre>";
    
		$this->data['namespace_urls'] = $nav['namespaces'];
		$this->data['view_urls'] = $nav['views'];
		$this->data['action_urls'] = $nav['actions'];
		$this->data['variant_urls'] = $nav['variants'];

		// Reverse horizontally rendered navigation elements
		if ( $this->data['rtl'] ) {
			$this->data['view_urls'] =
				array_reverse( $this->data['view_urls'] );
			$this->data['namespace_urls'] =
				array_reverse( $this->data['namespace_urls'] );
			$this->data['personal_urls'] =
				array_reverse( $this->data['personal_urls'] );
		}

		$this->data['pageLanguage'] =
			$this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode();

      
  }
  
  
  
	function execute() {
		// Suppress warnings to prevent notices about missing indexes in $this->data
    $this->getNav();
		wfSuppressWarnings();
		$this->html( 'headelement' );
    
		?>
    <div id="art-main">
      <header class="art-header">
        <?php echo $this->getIndicators(); ?>
        <div class="art-shapes">
          <div class="art-object1415556463"> </div>
        </div>
        <h1 class="art-headline sitesub">
          <?php echo wfMessage( 'subtitle' )->escaped(); $this->html( 'subtitle' ) ?>
        </h1>
        <h2 class="art-slogan sitetitle" role="banner">
          <a href="<?php echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] ) ?>">
          <?php echo wfMessage( 'sitetitle' )->escaped() ?></a>
        </h2>
        
      </header>
      				<a id="top"></a>
      <div class="art-sheet clearfix">
        <div class="art-layout-wrapper">

				<?php
				if ( $this->data['sitenotice'] ) {
					?>
					<div id="siteNotice"><?php
					$this->html( 'sitenotice' )
					?></div><?php
				}
				?>        
          <div class="art-content-layout">
            <div class="art-content-layout-row">
              <div class="art-layout-cell art-sidebar1">   
                <?php $this->renderPortals( $this->data['sidebar'] ); ?>
              </div>
              <div id="article" class="art-layout-cell art-content mw-body" role="main">  
                <nav class="art-nav">
                  <div class="art-nav-inner">
                    <ul class="art-hmenu">
                      <?php $this->renderNavigation( 'art-hmenu' ); ?>
                    </ul>
                    <ul class="art-hmenu" style="float:right;">
                      <?php $this->renderNavigation( ['ACTIONS',  'VIEWS', 'SEARCH' ] ); ?>
                      <li>
                        <a href="#"><b>&#8801;</b></a>
                        <?php $this->renderNavigation( 'PERSONAL' ); ?>
                      </li>
                      
                    </ul>                    
                  </div>
                </nav>
                <article class="art-post art-article">
                  <div class="art-postcontent art-postcontent-0 clearfix">
                    <div class='mw-topboxes'>
                      <div id="mw-js-message"
                        style="display:none;"<?php $this->html( 'userlangattributes' ) ?>></div>
                      <div class="mw-topbox" id="siteSub"><?php $this->msg( 'tagline' ) ?></div>
                      <?php
                      if ( $this->data['newtalk'] ) {
                        ?>
                        <div class="mw-usermessage mw-topbox"><?php $this->html( 'newtalk' ) ?></div>
                      <?php
                      }
                      ?>
                      <?php
                      if ( $this->data['sitenotice'] ) {
                        ?>
                        <div class="mw-topbox" id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div>
                      <?php
                      }
                      ?>
                    </div>
						<div id="mw-contentSub"<?php
						$this->html( 'userlangattributes' )
						?>><?php
							$this->html( 'subtitle' )
							?></div>

						<?php
						if ( $this->data['undelete'] ) {
							?>
							<div id="contentSub2"><?php $this->html( 'undelete' ) ?></div><?php
						}
						?>
                <?php
                $this->html( 'bodytext' );
                ?>
                  </div>
                </article>

              </div>
            </div>
          </div>
        </div>
      </div>
                 <?php
		$validFooterIcons = $this->getFooterIcons( "icononly" );
		$validFooterLinks = $this->getFooterLinks( "flat" ); // Additional footer links

		if ( count( $validFooterIcons ) + count( $validFooterLinks ) > 0 ) {
			?>
                <footer class="art-footer">
                  <div class="art-footer-inner" id="footer" role="contentinfo"<?php $this->html( 'userlangattributes' ) ?>>
			<?php
			$footerEnd = '</div></footer>';
		} else {
			$footerEnd = '';
		}

		foreach ( $validFooterIcons as $blockName => $footerIcons ) {
			?>
			<div id="f-<?php echo htmlspecialchars( $blockName ); ?>ico">
				<?php 
        foreach ( $footerIcons as $icon ) {  
          echo $this->getSkin()->makeFooterIcon( $icon ); 
        }
				?>
			</div>
		<?php
		}

		if ( count( $validFooterLinks ) > 0 ) {
			?>
			<ul id="f-list">
				<?php
				foreach ( $validFooterLinks as $aLink ) {
					?>
					<li id="<?php echo $aLink ?>"><?php $this->html( $aLink ) ?></li>
				<?php
				}
				?>
			</ul>
		<?php
		}

		echo $footerEnd;
		?>
      
    </div>
                
<?php
		$this->html( 'dataAfterContent' );
		$this->printTrail();
		echo "\n</body></html>";
		wfRestoreWarnings();
	}

	function searchFormx( $which ) {
		$search = $this->getSkin()->getRequest()->getText( 'search' );
		$action = $this->data['searchaction'];
		$s = "<form id=\"searchform-" . htmlspecialchars( $which )
			. "\" method=\"get\" class=\"inline\" action=\"$action\">";
		if ( $which == 'footer' ) {
			$s .= wfMessage( 'qbfind' )->text() . ": ";
		}

		$s .= $this->makeSearchInput( array(
			'class' => 'mw-searchInput',
			'type' => 'text',
			'size' => '14'
		) );
		$s .= ( $which == 'footer' ? " " : "<br />" );
		$s .= $this->makeSearchButton( 'go', array( 'class' => 'searchButton' ) );

		if ( $this->config->get( 'UseTwoButtonsSearchForm' ) ) {
			$s .= $this->makeSearchButton( 'fulltext', array( 'class' => 'searchButton' ) );
		} else {
			$s .= '<div><a href="' . $action . '" rel="search">'
				. wfMessage( 'powersearch-legend' )->escaped() . "</a></div>\n";
		}

		$s .= '</form>';

		return $s;
	}
  
  
	/**
	 * Render a series of portals
	 *
	 * @param array $portals
	 */
	protected function renderPortals( $portals ) {
		// Force the rendering of the following portals
		if ( !isset( $portals['SEARCH'] ) ) {
			$portals['SEARCH'] = true;
		}
		if ( !isset( $portals['TOOLBOX'] ) ) {
			$portals['TOOLBOX'] = true;
		}
		if ( !isset( $portals['LANGUAGES'] ) ) {
			$portals['LANGUAGES'] = true;
		}
		// Render portals
		foreach ( $portals as $name => $content ) {
			if ( $content === false ) { 
				continue;
			}

			// Numeric strings gets an integer when set as key, cast back - T73639
			$name = (string)$name;

			switch ( $name ) {
				case 'SEARCH':
					break;
				case 'TOOLBOX':
					$this->renderPortal( 'tb', $this->getToolbox(), 'toolbox', 'SkinTemplateToolboxEnd' );
					break;
				case 'LANGUAGES':
					if ( $this->data['language_urls'] !== false ) {
						$this->renderPortal( 'lang', $this->data['language_urls'], 'otherlanguages' );
					}
					break;
				default:
					$this->renderPortal( $name, $content );
					break;
			}
		}
	}

	/**
	 * @param string $name
	 * @param array $content
	 * @param null|string $msg
	 * @param null|string|array $hook
	 */
	protected function renderPortal( $name, $content, $msg = null, $hook = null ) {
		if ( $msg === null ) {
			$msg = $name;
		}
		$msgObj = wfMessage( $msg );
		$labelId = Sanitizer::escapeId( "p-$name-label" );
 /*
                <div class="">
                  <div class="">
                    <h3 class="t"><?php echo wfMessage( 'navigation-heading' )->escaped() ?></h3>
                  </div>
                  <div class="">
                    <ul class="art-vmenu">

*/ 

    if ( is_array( $content ) ) {
      $classes = ["art-vmenublock clearfix","art-vmenublockheader","art-vmenublockcontent"];
    } else {
      $classes = ["art-block clearfix","art-blockheader","art-blockcontent"];
    }
		?>
    
    
		<div class="<?php echo $classes[0]; ?>">
      <div class="<?php echo $classes[1]; ?>">
			<h3  class="t" id='<?php echo $labelId ?>'><?php
				echo htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $msg );
				?></h3>
      </div>
			<div class="<?php echo $classes[2]; ?>">
				<?php
				if ( is_array( $content ) ) {
					?>
					<ul class="art-vmenu">
						<?php
            
						foreach ( $content as $key => $val ) {
              $options = ['class'=> 'class="active"'];
              $sub = $val['sub'];
              unset($val['sub']);
							echo $this->makeListItem( $key, $val, $options );
              if (count($sub)>0) {
                ?>
                <ul class="active">
                <?php
            
						foreach ( $sub as $key1 => $val1 ) {
              $options = ['class'=> 'class="active"'];
              unset($val1['sub']);
              
							echo $this->makeListItem( "{$key}-{$key1}", $val1, $options );
						}
						?>
					</ul>
          <?php
              }
						}
						if ( $hook !== null ) {
							Hooks::run( $hook, [ &$this, true ] );
						}
						?>
					</ul>
				<?php
				} else {
					echo $content; /* Allow raw HTML block to be defined by extensions */
				}

				$this->renderAfterPortlet( $name );
				?>
			</div>
		</div>
	<?php
	}

	/**
	 * Render one or more navigations elements by name, automatically reveresed
	 * when UI is in RTL mode
	 *
	 * @param array $elements
	 */
	protected function renderNavigation( $elements ) {
		// If only one element was given, wrap it in an array, allowing more
		// flexible arguments
		if ( !is_array( $elements ) ) {
			$elements = [ $elements ];
			// If there's a series of elements, reverse them when in RTL mode
		} elseif ( $this->data['rtl'] ) {
			$elements = array_reverse( $elements );
		}
		// Render elements
		foreach ( $elements as $name => $element ) {
			switch ( $element ) {
				case 'art-hmenu':
          if (count($this->data['namespace_urls']) + count( $this->data['variant_urls'] ) > 1){
							foreach ( $this->data['namespace_urls'] as $link ) {
								?>
								<li <?php echo $link['attributes'] ?>><a <?php echo $link['class'] ?> href="<?php
										echo htmlspecialchars( $link['href'] )
										?>" <?php
										echo $link['key'];
										if ( isset ( $link['rel'] ) ) {
											echo ' rel="' . htmlspecialchars( $link['rel'] ) . '"';
										}
										?>><?php
											echo htmlspecialchars( $link['text'] )
											?></a></li>
							<?php
							}
							foreach ( $this->data['variant_urls'] as $link ) {
									?>
									<li><a <?php echo $link['class'] ?> href="<?php
										echo htmlspecialchars( $link['href'] )
										?>" lang="<?php
										echo htmlspecialchars( $link['lang'] )
										?>" hreflang="<?php
										echo htmlspecialchars( $link['hreflang'] )
										?>" <?php
										echo $link['key']
										?>><?php
											echo htmlspecialchars( $link['text'] )
											?></a></li>
								<?php
								}
          }
					break;
				case 'VIEWS':
							foreach ( $this->data['view_urls'] as $link ) {
								?>
								<li<?php echo $link['attributes'] ?>><a href="<?php
										echo htmlspecialchars( $link['href'] )
										?>" <?php
										echo $link['key'];
										if ( isset ( $link['rel'] ) ) {
											echo ' rel="' . htmlspecialchars( $link['rel'] ) . '"';
										}
										?>><?php
											// $link['text'] can be undefined - bug 27764
											if ( array_key_exists( 'text', $link ) ) {
												echo array_key_exists( 'img', $link )
													? '<img src="' . $link['img'] . '" alt="' . $link['text'] . '" />'
													: htmlspecialchars( $link['text'] );
											}
											?></a></li>
							<?php
							}
						break;
				case 'ACTIONS':
								foreach ( $this->data['action_urls'] as $link ) {
									?>
									<li<?php echo $link['attributes'] ?>>
										<a href="<?php
										echo htmlspecialchars( $link['href'] )
										?>" <?php
										echo $link['key'] ?>><?php echo htmlspecialchars( $link['text'] )
											?></a>
									</li>
								<?php
								}
					break;
				case 'PERSONAL':
					?>
						<ul<?php $this->html( 'userlangattributes' ) ?>>
							<?php

							$notLoggedIn = '';

							if ( !$this->getSkin()->getUser()->isLoggedIn() &&
								User::groupHasPermission( '*', 'edit' ) ){

								$notLoggedIn =
									Html::rawElement( 'li',
										[ 'id' => 'pt-anonuserpage' ],
										$this->getMsg( 'notloggedin' )->escaped()
									);

							}

							$personalTools = $this->getPersonalTools();

							$langSelector = '';
							if ( array_key_exists( 'uls', $personalTools ) ) {
								$langSelector = $this->makeListItem( 'uls', $personalTools[ 'uls' ] );
								unset( $personalTools[ 'uls' ] );
							}

							if ( !$this->data[ 'rtl' ] ) {
								echo $langSelector;
								echo $notLoggedIn;
							}

							foreach ( $personalTools as $key => $item ) {
								echo $this->makeListItem( $key, $item );
							}

							if ( $this->data[ 'rtl' ] ) {
								echo $notLoggedIn;
								echo $langSelector;
							}
							?>
						</ul>
					<?php
					break;
				case 'SEARCH':
					?> <li>
              <form action="<?php $this->text( 'wgScript' ) ?>" id="searchform" class="art-search" method="get" name="searchform">
 							<?php
							echo $this->makeSearchInput( [ 'id' => 'searchInput' ] );
							echo Html::hidden( 'title', $this->get( 'searchtitle' ) );
							// We construct two buttons (for 'go' and 'fulltext' search modes),
							// but only one will be visible and actionable at a time (they are
							// overlaid on top of each other in CSS).
							// * Browsers will use the 'fulltext' one by default (as it's the
							//   first in tree-order), which is desirable when they are unable
							//   to show search suggestions (either due to being broken or
							//   having JavaScript turned off).
							// * The mediawiki.searchSuggest module, after doing tests for the
							//   broken browsers, removes the 'fulltext' button and handles
							//   'fulltext' search itself; this will reveal the 'go' button and
							//   cause it to be used.
	//						echo $this->makeSearchButton(
				//				'fulltext', [ 'id' => 'mw-searchButton', 'class' => 'searchButton mw-fallbackSearchButton' ]
			//				);
							echo $this->makeSearchButton(
								'go', [ 'id' => 'searchButton', 'class' => 'searchButton' ]
							);
							?>
						</form>
					</li>
					<?php

					break;
			}
		}
	}  
}
