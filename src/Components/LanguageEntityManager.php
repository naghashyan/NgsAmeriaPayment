<?php declare(strict_types=1);

namespace Ngs\AmeriaPayment\Components;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleEntity;

/**
 * Class LanguageManager contains SW language related functionality
 */
class LanguageEntityManager extends BaseEntityManager
{
    /**
     * Get locale code by id
     *
     * @param string $id
     * @param Context $context
     *
     * @return string|null
     */
    public function getLocaleCodeById(string $id, Context $context): ?string
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('locale');

        $languageEntity = $this->repository->search($criteria, $context)->first();

        if ($languageEntity instanceof LanguageEntity && $languageEntity->getLocale() instanceof LocaleEntity) {
            return $languageEntity->getLocale()->getCode();
        }

        return null;
    }

}