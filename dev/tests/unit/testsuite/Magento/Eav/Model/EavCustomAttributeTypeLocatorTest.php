<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;

class EavCustomAttributeTypeLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EavCustomAttributeTypeLocator
     */
    private $eavCustomAttributeTypeLocator;

    /**
     * @var AttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepository = [];

    /**
     * @var array
     */
    private $serviceEntityTypeMap = [];

    /**
     * @var array
     */
    private $serviceBackendModelDataInterfaceMapMock;


    protected function setUp()
    {
        $this->attributeRepository = $this->getMock(
            'Magento\Eav\Model\AttributeRepository',
            ['get'],
            [],
            '',
            false
        );
    }

    /**
     * Test getType method
     *
     * @param string $attributeCode
     * @param string $serviceClass
     * @param array $attributeRepositoryResponse
     * @param array $serviceEntityTypeMapData
     * @param array $serviceBackendModelDataInterfaceMapData
     * @param string $expected
     * @dataProvider getTypeDataProvider
     */
    public function testGetType(
        $attributeCode,
        $serviceClass,
        $attributeRepositoryResponse,
        $serviceEntityTypeMapData,
        $serviceBackendModelDataInterfaceMapData,
        $expected
    ) {
        $this->attributeRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn($attributeRepositoryResponse);


        $this->eavCustomAttributeTypeLocator = new EavCustomAttributeTypeLocator(
            $this->attributeRepository,
            $serviceEntityTypeMapData,
            $serviceBackendModelDataInterfaceMapData
        );

        $type = $this->eavCustomAttributeTypeLocator->getType($attributeCode, $serviceClass);

        $this->assertEquals($expected, $type, 'Expected: ' . $expected . 'but got: ' . $type);
    }

    public function getTypeDataProvider()
    {
        $serviceInterface = 'Magento\Catalog\Api\Data\ProductInterface';
        $eavEntityType = 'catalog_product';
        $mediaBackEndModelClass = 'Magento\Catalog\Model\Product\Attribute\Backend\Media';
        $mediaAttributeDataInterface = '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface';
        $serviceBackendModelDataInterfaceMapData = [
            $serviceInterface => [$mediaBackEndModelClass => $mediaAttributeDataInterface]
        ];

        $attribute = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            ['getBackendModel'],
            [],
            '',
            false
        );

        $attribute->expects($this->any())
            ->method('getBackendModel')
            ->willReturn($mediaBackEndModelClass);

        $attributeNoBackendModel = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            ['getBackendModel'],
            [],
            '',
            false
        );

        $attributeNoBackendModel->expects($this->any())
            ->method('getBackendModel')
            ->willReturn(null);

        return [
            [
                'attributeCode' => 'media_galley',
                'serviceClass' => $serviceInterface,
                'attributeRepositoryResponse' => $attribute,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => $serviceBackendModelDataInterfaceMapData,
                'expected' => $mediaAttributeDataInterface
            ],
            [
                'attributeCode' => null,
                'serviceClass' => $serviceInterface,
                'attributeRepositoryResponse' => $attribute,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => $serviceBackendModelDataInterfaceMapData,
                'expected' => null
            ],
            [
                'attributeCode' => 'media_galley',
                'serviceClass' => null,
                'attributeRepositoryResponse' => $attribute,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => $serviceBackendModelDataInterfaceMapData,
                'expected' => null
            ],
            [
                'attributeCode' => 'media_galley',
                'serviceClass' => $serviceInterface,
                'attributeRepositoryResponse' => $attributeNoBackendModel,
                'serviceEntityTypeMapData' => [],
                'serviceBackendModelDataInterfaceMapData' => [],
                'expected' => null
            ],
            [
                'attributeCode' => 'media_galley',
                'serviceClass' => 'Magento\Catalog\Api\Data\ProductInterface',
                'attributeRepositoryResponse' => $attribute,
                'serviceEntityTypeMapData' => [$serviceInterface => $eavEntityType],
                'serviceBackendModelDataInterfaceMapData' => [],
                'expected' => null
            ]
        ];
    }
}
