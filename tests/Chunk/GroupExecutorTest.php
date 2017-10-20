<?php

namespace DalaiLomo\ACE\Tests\Group;

use DalaiLomo\ACE\Group\GroupExecutor;
use DalaiLomo\ACE\Config\ACEConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

class GroupExecutorTest extends TestCase
{
    public function executionsByKeyProvider()
    {
        return [
            'key fooKey' => [
                'fooKey', [
                    'fooGroup' => [
                        0 => [
                            'echo "a"' => [
                                'stdout' => "a\n"
                            ]
                        ],
                        1 => [
                            'echo "b"' => [
                                'stdout' => "b\n"
                            ]
                        ],
                        2 => [
                            'idontexist' => [
                                'stderr' => "sh: idontexist: command not found\n"
                            ]
                        ],
                        3 => [
                            'echo "c"' => [
                                'stdout' => "c\n"
                            ]
                        ],
                    ]
                ]
            ],
            'key barKey' => [
                'barKey', [
                    'bazGroup' => [
                        0 => [
                            'echo "hello"' => [
                                'stdout' => "hello\n"
                            ]
                        ],
                        1 => [
                            'echo "world"' => [
                                'stdout' => "world\n"
                            ]
                        ],
                        2 => [
                            'echo "on fire"' => [
                                'stdout' => "on fire\n"
                            ]
                        ],
                    ],
                    'booGroup' => [
                        0 => [
                            'echo "fantasmikos"' => [
                                'stdout' => "fantasmikos\n"
                            ]
                        ],
                        1 => [
                            'echo "in the night"' => [
                                'stdout' => "in the night\n"
                            ]
                        ],
                        2 => [
                            'echo "oscura"' => [
                                'stdout' => "oscura\n"
                            ]
                        ],
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider executionsByKeyProvider
     */
    public function testShouldExecuteCorrespondingGroupsByGivenKey($key, $expectedOutput) {
        $groupExecutor = new GroupExecutor(
            new ACEConfig(__DIR__ . '/../configtest.yml'),
            $key,
            $this->getMockBuilder(Input::class)->getMock(),
            $this->getMockBuilder(Output::class)->getMock()
        );

        $groupExecutor->executeProcessGroups();

        // the pids need to be faked as is quite hard to figure out which ones will be on the data provider,
        // unless you have superpowers. Anyway the implementation for this should be changed as is kinda
        // like a pain in the arse to test it...
        $fakedPidsCommandsOutput = $this->fakePids($groupExecutor->getCommandsOutput());

        $this->assertEquals($expectedOutput, $fakedPidsCommandsOutput);
    }

    private function fakePids(array $commandsOutput)
    {
        $commandsOutput = array_map('array_values', $commandsOutput);
        return $commandsOutput;
    }
}
