<?php

namespace Base\Notary\Security;

use Base\Office\Entity\Share\Document;
use Base\Office\Share\AudienceResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Who of the office reads a client's document, beyond the client and
 * whoever sent it (the voter's own business):
 *
 * - the notaries and the clerks, who draw up the deeds, see and read it;
 * - the rest of the staff (the reception) sees that it exists and what it
 *   is called, and reads only what is not marked confidential;
 * - nobody else: neither another client, nor the site's administrators as
 *   such.
 *
 * Only its sender withdraws a document.
 */
final class OfficeAudience implements AudienceResolverInterface
{
    public function __construct(
        private readonly RoleHierarchyInterface $roles,
        #[Autowire('%notary.roles.notary%')] private readonly string $notaryRole = 'ROLE_NOTARY',
        #[Autowire('%notary.roles.clerk%')] private readonly string $clerkRole = 'ROLE_CLERK',
        #[Autowire('%notary.roles.staff%')] private readonly string $staffRole = 'ROLE_STAFF',
    ) {
    }

    public function decide(string $attribute, Document $document, UserInterface $user): ?bool
    {
        if (self::REVOKE === $attribute || null === $document->getRecipient()) {
            return null;
        }
        $held = $this->roles->getReachableRoleNames($user->getRoles());
        if (!\in_array($this->staffRole, $held, true)) {
            return null;
        }
        if (\in_array($this->notaryRole, $held, true) || \in_array($this->clerkRole, $held, true)) {
            return true;
        }

        return self::VIEW === $attribute || !$document->isConfidential() ? true : null;
    }
}
