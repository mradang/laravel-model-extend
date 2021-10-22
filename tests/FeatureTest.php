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
            'age'=> 28,
            'titles' => ['副主任', '工程师'],
        ];

        // 新增
        $user = User::create($data);
        $this->assertTrue(Str::startsWith($user->getChangeMessage(), '创建：'));
        $user_data = json_decode(Str::after($user->getChangeMessage(), '创建：'), true);
        $this->assertSame($data, $user_data);

        // 修改
        $data['name'] = '李四';
        $data['age'] = 19;
        $data['titles'] = ['助理工程师'];
        $user->fill($data);
        $user->save();
        $this->assertTrue(Str::startsWith($user->getChangeMessage(), '更新：'));
        $msg = sprintf(
            '更新：「name」由「张三」改为「李四」, 「age」由「28」改为「19」, 「titles」由「%s」改为「%s」',
            json_encode(['副主任', '工程师'], JSON_UNESCAPED_UNICODE),
            json_encode(['助理工程师'], JSON_UNESCAPED_UNICODE),
        );
        $this->assertEquals($msg, $user->getChangeMessage());

        $data['titles'] = [];
        $user->titles = [];
        $user->save();
        $this->assertTrue(Str::startsWith($user->getChangeMessage(), '更新：'));
        $msg = sprintf(
            '更新：「titles」由「%s」改为「%s」',
            json_encode(['助理工程师'], JSON_UNESCAPED_UNICODE),
            json_encode([], JSON_UNESCAPED_UNICODE),
        );
        $this->assertEquals($msg, $user->getChangeMessage());

        // 删除
        $user->delete();
        $this->assertTrue(Str::startsWith($user->getChangeMessage(), '删除：'));
        $user_data = json_decode(Str::after($user->getChangeMessage(), '删除：'), true);
        $user_data = Arr::only($user_data, array_keys($data));
        $this->assertSame($data, $user_data);
    }
}
