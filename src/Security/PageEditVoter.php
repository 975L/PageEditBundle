<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Security;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\PageEditBundle\Entity\PageEdit;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for PageEdit access
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditVoter extends Voter
{
    /**
     * Stores current Request
     */
    private readonly ?\Symfony\Component\HttpFoundation\Request $request;

    /**
     * Used for access to archived
     * @var string
     */
    final public const ARCHIVED = 'c975LPageEdit-archived';

    /**
     * Used for access to archived-delete
     * @var string
     */
    final public const ARCHIVED_DELETE = 'c975LPageEdit-archived-delete';

    /**
     * Used for access to config
     * @var string
     */
    final public const CONFIG = 'c975LPageEdit-config';

    /**
     * Used for access to create
     * @var string
     */
    final public const CREATE = 'c975LPageEdit-create';

    /**
     * Used for access to dashboard
     * @var string
     */
    final public const DASHBOARD = 'c975LPageEdit-dashboard';

    /**
     * Used for access to delete
     * @var string
     */
    final public const DELETE = 'c975LPageEdit-delete';

    /**
     * Used for access to deleted
     * @var string
     */
    final public const DELETED = 'c975LPageEdit-deleted';

    /**
     * Used for access to deleted-delete
     * @var string
     */
    final public const DELETED_DELETE = 'c975LPageEdit-deleted-delete';

    /**
     * Used for access to display
     * @var string
     */
    final public const DISPLAY = 'c975LPageEdit-display';

    /**
     * Used for access to duplicate
     * @var string
     */
    final public const DUPLICATE = 'c975LPageEdit-duplicate';

    /**
     * Used for access to help
     * @var string
     */
    final public const HELP = 'c975LPageEdit-help';

    /**
     * Used for access to links
     * @var string
     */
    final public const LINKS = 'c975LPageEdit-links';

    /**
     * Used for access to modify
     * @var string
     */
    final public const MODIFY = 'c975LPageEdit-modify';

    /**
     * Used for access to redirected
     * @var string
     */
    final public const REDIRECTED = 'c975LPageEdit-redirected';

    /**
     * Used for access to redirected-delete
     * @var string
     */
    final public const REDIRECTED_DELETE = 'c975LPageEdit-redirected-delete';

    /**
     * Used for access to slug
     * @var string
     */
    final public const SLUG = 'c975LPageEdit-slug';

    /**
     * Used for access to upload
     * @var string
     */
    final public const UPLOAD = 'c975LPageEdit-upload';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
    private const ATTRIBUTES = [self::ARCHIVED, self::ARCHIVED_DELETE, self::CONFIG, self::CREATE, self::DASHBOARD, self::DELETE, self::DELETED, self::DELETED_DELETE, self::DISPLAY, self::DUPLICATE, self::HELP, self::LINKS, self::MODIFY, self::REDIRECTED, self::REDIRECTED_DELETE, self::SLUG, self::UPLOAD];

    public function __construct(
        /**
         * Stores ConfigServiceInterface
         */
        private readonly ConfigServiceInterface $configService,
        /**
         * Stores AccessDecisionManagerInterface
         */
        private readonly AccessDecisionManagerInterface $decisionManager,
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        if (null !== $subject) {
            return $subject instanceof PageEdit && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::ARCHIVED, self::ARCHIVED_DELETE, self::CONFIG, self::CREATE, self::DASHBOARD, self::DELETE, self::DELETED, self::DELETED_DELETE, self::DISPLAY, self::DUPLICATE, self::HELP, self::LINKS, self::MODIFY, self::REDIRECTED, self::REDIRECTED_DELETE, self::SLUG => $this->hasRoleNeeded($token),
            self::UPLOAD => $this->isUploadAllowed($token),
            default => throw new LogicException('Invalid attribute: ' . $attribute),
        };
    }

    /**
     * Checks if user has roleNeeded
     * @return bool
     */
    public function hasRoleNeeded($token)
    {
        return $this->decisionManager->decide($token, [$this->configService->getParameter('c975LPageEdit.roleNeeded', 'c975l/pageedit-bundle')]);
    }

    /**
     * Checks if upload is allowed
     * @return bool
     */
    public function isUploadAllowed($token)
    {
        //Checks origin - https://www.tinymce.com/docs/advanced/php-upload-handler/ (same origin won't set an origin)
        return $this->hasRoleNeeded($token) && null === $this->request->server->get('HTTP_ORIGIN');
    }
}
