<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Security;

use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use c975L\PageEditBundle\Entity\PageEdit;

/**
 * Voter for PageEdit access
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditVoter extends Voter
{
    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * The role needed to be allowed access (defined in config)
     * @var string
     */
    private $roleNeeded;

    /**
     * Used for access to archived
     * @var string
     */
    public const ARCHIVED = 'c975LPageEdit-archived';

    /**
     * Used for access to archived-delete
     * @var string
     */
    public const ARCHIVED_DELETE = 'c975LPageEdit-archived-delete';

    /**
     * Used for access to create
     * @var string
     */
    public const CREATE = 'c975LPageEdit-create';

    /**
     * Used for access to dashboard
     * @var string
     */
    public const DASHBOARD = 'c975LPageEdit-dashboard';

    /**
     * Used for access to delete
     * @var string
     */
    public const DELETE = 'c975LPageEdit-delete';

    /**
     * Used for access to deleted
     * @var string
     */
    public const DELETED = 'c975LPageEdit-deleted';

    /**
     * Used for access to deleted-delete
     * @var string
     */
    public const DELETED_DELETE = 'c975LPageEdit-deleted-delete';

    /**
     * Used for access to display
     * @var string
     */
    public const DISPLAY = 'c975LPageEdit-display';

    /**
     * Used for access to duplicate
     * @var string
     */
    public const DUPLICATE = 'c975LPageEdit-duplicate';

    /**
     * Used for access to help
     * @var string
     */
    public const HELP = 'c975LPageEdit-help';

    /**
     * Used for access to links
     * @var string
     */
    public const LINKS = 'c975LPageEdit-links';

    /**
     * Used for access to modify
     * @var string
     */
    public const MODIFY = 'c975LPageEdit-modify';

    /**
     * Used for access to redirected
     * @var string
     */
    public const REDIRECTED = 'c975LPageEdit-redirected';

    /**
     * Used for access to redirected-delete
     * @var string
     */
    public const REDIRECTED_DELETE = 'c975LPageEdit-redirected-delete';

    /**
     * Used for access to slug
     * @var string
     */
    public const SLUG = 'c975LPageEdit-slug';

    /**
     * Used for access to upload
     * @var string
     */
    public const UPLOAD = 'c975LPageEdit-upload';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
    private const ATTRIBUTES = array(
        self::ARCHIVED,
        self::ARCHIVED_DELETE,
        self::CREATE,
        self::DASHBOARD,
        self::DELETE,
        self::DELETED,
        self::DELETED_DELETE,
        self::DISPLAY,
        self::DUPLICATE,
        self::HELP,
        self::LINKS,
        self::MODIFY,
        self::REDIRECTED,
        self::REDIRECTED_DELETE,
        self::SLUG,
        self::UPLOAD,
    );

    public function __construct(AccessDecisionManagerInterface $decisionManager, string $roleNeeded)
    {
        $this->decisionManager = $decisionManager;
        $this->roleNeeded = $roleNeeded;
    }

    /**
     * Checks if attribute and subject are supported
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof PageEdit && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * Votes if access is granted
     * @return bool
     * @throws \LogicException
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //Defines access rights
        switch ($attribute) {
            case self::ARCHIVED:
            case self::ARCHIVED_DELETE:
            case self::CREATE:
            case self::DASHBOARD:
            case self::DELETE:
            case self::DELETED:
            case self::DELETED_DELETE:
            case self::DISPLAY:
            case self::DUPLICATE:
            case self::HELP:
            case self::LINKS:
            case self::MODIFY:
            case self::REDIRECTED:
            case self::REDIRECTED_DELETE:
            case self::SLUG:
            case self::UPLOAD:
                return $this->decisionManager->decide($token, array($this->roleNeeded));
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }
}