<?php

namespace mradang\LaravelModelExtend\Test;

use Illuminate\Support\Arr;

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

        // 验证新增数据的情况
        $changes = [
            'name' => ['old_value' => null, 'new_value' => '张三'],
            'age' => ['old_value' => null, 'new_value' => 28],
            'titles' => [
                'old_value' => 'null',
                'new_value' => json_encode(['副主任', '工程师'], JSON_UNESCAPED_UNICODE),
            ],
        ];
        $this->assertEquals($user->getModelChanges()->toArray(), $changes);
        $msg = sprintf(
            '「name」由「」改为「张三」，「age」由「」改为「28」，「titles」由「null」改为「%s」',
            json_encode(['副主任', '工程师'], JSON_UNESCAPED_UNICODE),
        );
        $this->assertEquals($msg, $user->getChangeMessage());

        // 变更数据
        $data['name'] = '李四';
        $data['age'] = 19;
        $data['titles'] = ['助理工程师'];
        $user->fill($data);
        $user->save();

        // 验证变更字段
        $changes = [
            'name' => ['old_value' => '张三', 'new_value' => '李四'],
            'age' => ['old_value' => 28, 'new_value' => 19],
            'titles' => [
                'old_value' => json_encode(['副主任', '工程师'], JSON_UNESCAPED_UNICODE),
                'new_value' => json_encode(['助理工程师'], JSON_UNESCAPED_UNICODE),
            ],
        ];
        $this->assertEquals($user->getModelChanges()->toArray(), $changes);
        $this->assertEquals(
            $user->getModelChanges(['name', 'titles'])->toArray(),
            Arr::only($changes, ['name', 'titles']),
        );
        $this->assertEquals(
            $user->getModelChanges('name')->toArray(),
            Arr::only($changes, ['name']),
        );

        // 验证变更信息
        $msg = sprintf(
            '「name」由「张三」改为「李四」，「age」由「28」改为「19」，「titles」由「%s」改为「%s」',
            json_encode(['副主任', '工程师'], JSON_UNESCAPED_UNICODE),
            json_encode(['助理工程师'], JSON_UNESCAPED_UNICODE),
        );
        $this->assertEquals($msg, $user->getChangeMessage());
        $msg = sprintf(
            '「titles」由「%s」改为「%s」',
            json_encode(['副主任', '工程师'], JSON_UNESCAPED_UNICODE),
            json_encode(['助理工程师'], JSON_UNESCAPED_UNICODE),
        );
        $this->assertEquals($msg, $user->getChangeMessage(['titles']));
        $msg = '「name」由「张三」改为「李四」';
        $this->assertEquals($msg, $user->getChangeMessage('name'));

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
