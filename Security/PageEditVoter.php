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

class PageEditVoter extends Voter
{
    private $decisionManager;
    private $roleNeeded;

    public const ARCHIVED = 'archived';
    public const ARCHIVED_DELETE = 'archived-delete';
    public const CREATE = 'create';
    public const DASHBOARD = 'dashboard';
    public const DELETE = 'delete';
    public const DELETED = 'deleted';
    public const DELETED_DELETE = 'deleted-delete';
    public const DISPLAY = 'display';
    public const DUPLICATE = 'duplicate';
    public const HELP = 'help';
    public const LINKS = 'links';
    public const MODIFY = 'modify';
    public const REDIRECTED = 'redirected';
    public const REDIRECTED_DELETE = 'redirected-delete';
    public const SLUG = 'slug';
    public const UPLOAD = 'upload';

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

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof PageEdit && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

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
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }
}
