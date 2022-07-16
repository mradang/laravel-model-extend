<?php

namespace mradang\LaravelModelExtend\Test;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FeatureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @covers ModelChangeMessageTrait::getChangeMessage
     */
    public function testBasicFeatures()
    {
        $data = [
            'name' => '张三',
            'age' => 28,
            'titles' => ['副主任', '工程师'],
        ];

        $user = User::create($data);

        // 验证变更信息
        $data['name'] = '李四';
        $data['age'] = 19;
        $data['titles'] = ['助理工程师'];
        $user->fill($data);
        $user->save();
        $msg = sprintf(
            '「name」由「张三」改为「李四」, 「age」由「28」改为「19」, 「titles」由「%s」改为「%s」',
            json_encode(['副主任', '工程师'], JSON_UNESCAPED_UNICODE),
            json_encode(['助理工程师'], JSON_UNESCAPED_UNICODE),
        );
        $this->assertEquals($msg, $user->getChangeMessage());

        // 验证数组值置空
        $data['titles'] = [];
        $user->titles = [];
        $user->save();
        $msg = sprintf(
            '「titles」由「%s」改为「%s」',
            json_encode(['助理工程师'], JSON_UNESCAPED_UNICODE),
            json_encode([], JSON_UNESCAPED_UNICODE),
        );
        $this->assertEquals($msg, $user->getChangeMessage());
    }
}
