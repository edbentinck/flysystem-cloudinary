<?php

namespace Enl\Flysystem\Cloudinary\Test\AdapterAction;

use Cloudinary\Error;
use League\Flysystem\Config;

class WriteTest extends ActionTestCase
{
    public function testReturnsFalseOnFailure()
    {
        list($cloudinary, $api) = $this->buildAdapter();
        $api->upload('path', 'contents', false)
            ->shouldBeCalled()
            ->willThrow(Error::class);

        $this->assertFalse($cloudinary->write('path', 'contents', new Config()));
        $this->assertFalse($cloudinary->writeStream('path', tmpfile(), new Config()));
    }

    /**
     * @dataProvider writeParametersProvider
     */
    public function testReturnsNormalizedMetadataOnSuccess($method, $path, $content)
    {
        list($cloudinary, $api) = $this->buildAdapter();
        $api->upload($path, is_resource($content) ? stream_get_contents($content) : $content, false)->willReturn([
            'public_id' => $path,
            'bytes' => 123123
        ]);
        $response = $cloudinary->$method($path, $content, new Config());

        $this->assertEquals(123123, $response['size']);
        $this->assertEquals('test-path', $response['path']);
    }

    public function writeParametersProvider()
    {
        return [
            'write' => ['write', 'test-path', 'content'],
            'writeStream' => ['writeStream', 'test-path', tmpfile()]
        ];
    }
}
