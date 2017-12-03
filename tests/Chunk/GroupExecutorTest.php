<?php

namespace DalaiLomo\ACE\Tests\Group;

use DalaiLomo\ACE\Group\GroupExecutor;
use DalaiLomo\ACE\Config\ACEConfig;
use PHPUnit\Framework\TestCase;
use RomaricDrigon\MetaYaml\Loader\YamlLoader;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

class GroupExecutorTest extends TestCase
{
    public function groupsShouldBeFoundUnderCorrespondingKeyProvider()
    {
        return [
            'Groups found on key foo' => [
                'fooKey', ['fooGroup']
            ],
            'Groups found on key bar' => [
                'barKey', ['bazGroup', 'booGroup']
            ]
        ];
    }

    /**
     * @dataProvider groupsShouldBeFoundUnderCorrespondingKeyProvider
     */
    public function testGroupsShouldBeFoundUnderCorrespondingKey($key, array $expectedGroups)
    {
        $groupExecutor = $this->createGroupExecutorInstance($key);
        $commandsOutput = $groupExecutor->getCommandsOutput();

        foreach ($expectedGroups as $expectedGroup) {
            $this->assertArrayHasKey($expectedGroup, $commandsOutput);
        }
    }

    public function commandOutputsShouldBeFoundUnderCorrespondingGroupProvider()
    {
        return [
            'Commands on fooKey:fooGroup' => [
                'fooKey', 'fooGroup', [
                    'echo "a"' => [
                        'stdout' => "a\n"
                    ],
                    'echo "b"' => [
                        'stdout' => "b\n"
                    ],
                    'echo "c"' => [
                        'stdout' => "c\n"
                    ]
                ]
            ],
            'Commands on barKey:bazGroup' => [
                'barKey', 'bazGroup', [
                    'echo "hello"' => [
                        'stdout' => "hello\n"
                    ],
                    'echo "world"' => [
                        'stdout' => "world\n"
                    ],
                    'echo "on fire"' => [
                        'stdout' => "on fire\n"
                    ],
                ]
            ],
            'Commands on barKey:booGroup' => [
                'barKey', 'booGroup', [
                    'echo "fantasmikos"' => [
                        'stdout' => "fantasmikos\n"
                    ],
                    'echo "in the night"' => [
                        'stdout' => "in the night\n"
                    ],
                    'echo "oscura"' => [
                        'stdout' => "oscura\n"
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider commandOutputsShouldBeFoundUnderCorrespondingGroupProvider
     */
    public function testCommandOutputsShouldBeFoundUnderCorrespondingGroup($key, $group, array $expectedCommandStreamsCollection)
    {
        $groupExecutor = $this->createGroupExecutorInstance($key);
        $commandsOutput = $groupExecutor->getCommandsOutput();

        foreach($commandsOutput[$group] as $pid => $commandStreams) {
            $this->assertTrue(is_int($pid), 'The pid should be an integer');

            $commandStreamsKey = key($commandStreams);
            $this->assertTrue(array_key_exists($commandStreamsKey, $expectedCommandStreamsCollection));
            $this->assertEquals($commandStreams[$commandStreamsKey], $expectedCommandStreamsCollection[$commandStreamsKey]);
        }
    }

    private function createGroupExecutorInstance($key)
    {
        $ge = new GroupExecutor(
            new ACEConfig(__DIR__ . '/../configtest.yml', new YamlLoader()),
            $key,
            $this->getMockBuilder(Input::class)->getMock(),
            $this->getMockBuilder(Output::class)->getMock()
        );

        $ge->executeProcessGroups();

        return $ge;
    }

    private function fakePids(array $commandsOutput)
    {
        $commandsOutput = array_map('array_values', $commandsOutput);
        return $commandsOutput;
    }
}
