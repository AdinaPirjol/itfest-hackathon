<?php

namespace Project\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BulkCreateUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('users:bulk')
            ->setDescription('Bulk crete user accounts via uploaded file')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Filename'
            )
            ->addArgument(
                'role',
                InputArgument::REQUIRED,
                'User role'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $role = $input->getArgument('role');

        $fileName = $input->getArgument('filename');
        $uploadPath = $this->getContainer()->getParameter('import_directory');

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($uploadPath . $fileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($uploadPath . $fileName);
        } catch (\Exception $e) {
            throw new \Exception('Error loading file "' . pathinfo($fileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = "D";
        $data = [];

        //  Loop through each row of the worksheet in turn
        for ($row = 1; $row <= $highestRow; $row++) {
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray(
                'A' . $row . ':' . $highestColumn . $row,
                null,
                true,
                false
            );
            $data[] = $rowData;
        }

        /** @var \Project\UserBundle\Services\UserService $userService */
        $userService = $this->getContainer()->get(\Project\UserBundle\Services\UserService::ID);

        $errors = [];
        foreach ($data as $row) {
            $cols = reset($row);

            if ($cols[0] == 'username' || is_null($cols[0])) {
                continue;
            }

            $params = array(
                'username' => $cols[0],
                'firstname' => $cols[1],
                'lastname' => $cols[2],
                'email' => $cols[3],
                'role' => $role
            );

            $response = $userService->createUser($params);

            if ($response['error'] === true) {
                $errors[] = $response['message'];
            }
        }

        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove(array($uploadPath . $fileName));

        return $errors;
    }
}