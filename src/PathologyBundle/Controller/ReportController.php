<?php

namespace PathologyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PathologyBundle\Entity\Report;
use PathologyBundle\Form\ReportType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ReportController extends Controller
{
    public function createReportAction(Request $request)
    {
        // Build the form.
        $report = new Report();
        $form = $this->createForm(ReportType::class, $report);

        // Retrieve patient details.
        $patient_id = trim($request->get('patientId'));
        $patient = $this->getDoctrine()
            ->getRepository('PathologyBundle:User')
            ->find($patient_id);

        // Show patients and test details.
        $patient_details['name'] = $patient->getFname() . ' ' . $patient->getLname();
        $patient_details['email'] = $patient->getEmail();
        $patient_details['phone'] = $patient->getPhoneNumber();

        // Add custom form elements for entering test readings.
        $tests = $patient->getPathologyTest();
        foreach ($tests as $test) {
            $test_id = $test->getId();
            $patient_details['tests'][$test->getId()] = array(
                'name' => $test->getName(),
                'referenceValue' => $test->getReferenceValue(),
                'unit' => $test->getUnit(),
                'day1' => time(),
                'day2' => strtotime('+1 day'),
                'day3' => strtotime('+2 day'),
                'id' => $test_id,
            );
            $form->add('day1_' . $test_id, TextType::class, array('mapped' => false));
            $form->add('day2_' . $test_id, TextType::class, array('mapped' => false));
            $form->add('day3_' . $test_id, TextType::class, array('mapped' => false));
        }

        // Handle the submit (will only happen on POST).
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Prepare reports data array for storing in Report entity.
            foreach ($tests as $test) {
                $test_id = $test->getId();
                $reports_data[$test->getId()] = array(
                    'name' => $test->getName(),
                    'referenceValue' => $test->getReferenceValue(),
                    'unit' => $test->getUnit(),
                    'day1' => array(
                        'timestamp' => time(),
                        'value' => $form['day1_' . $test_id]->getData(),
                    ),
                    'day2' => array(
                        'timestamp' => strtotime('+1 day'),
                        'value' => $form['day2_' . $test_id]->getData(),
                    ),
                    'day3' => array(
                        'timestamp' => strtotime('+2 day'),
                        'value' => $form['day3_' . $test_id]->getData(),
                    ),
                );
            }

            // Set Reports fields.
            $report->setPatient($patient);
            $report->setTestReports($reports_data);
            $time = new \DateTime();
            $report->setCreated($time);
            $report->setUpdated($time);

            // Flush Report entity to DB.
            $em = $this->getDoctrine()->getManager();
            $em->persist($report);
            $em->flush();

            return $this->redirectToRoute('view_patients');
        }

        return $this->render('PathologyBundle:Report:create_report.html.twig', array(
            'form' => $form->createView(),
            'patientDetails' => $patient_details,
        ));
    }

    /**
     * View all reports action by patient.
     */
    public function viewReportsAction(Request $request)
    {
        // Parse query parameter.
        $query = $request->getQueryString();
        parse_str($query, $parameters);
        if (empty($parameters['message'])) {
            $parameters['message'] = '';
        }
        // Retrieve patient details.
        $patient_id = trim($request->get('patientId'));
        $patient = $this->getDoctrine()
            ->getRepository('PathologyBundle:User')
            ->find($patient_id);

        // Retrieve report details.
        $report_id = trim($request->get('reportId'));
        $reports = $this->getDoctrine()
            ->getRepository('PathologyBundle:Report')
            ->findReportsByPatient($patient);

        $reports_data = array();

        if (!empty($reports)) {
            $reports_data['patient_name'] = $patient->getFname() . ' ' . $patient->getLname();
            $reports_data['email'] = $patient->getEmail();
            $reports_data['phone'] = $patient->getPhoneNumber();
            $reports_data['patient_id'] = $patient_id;

            foreach ($reports as $report) {
                $reports_data['reports'][] = array(
                    'created' => $report->getCreated(),
                    'id' => $report->getId(),
                );
            }
        }

        // Show template on the basic user role.
        $current_user = $this->get('security.token_storage')->getToken()->getUser();
        $roles = $current_user->getRoles();

        if (in_array('ROLE_PATIENT', $roles)) {
             return $this->render('PathologyBundle:Report:view_reports_patient.html.twig', array(
                'reportsData' => $reports_data,
                'headers' => $headers,
                'message' => $parameters['message'],
            ));
        }

        return $this->render('PathologyBundle:Report:view_reports.html.twig', array(
            'reportsData' => $reports_data,
            'headers' => $headers
        ));
    }

    /**
     * View Detail report.
     */
    public function viewReportAction(Request $request)
    {
        $patient_id = trim($request->get('patientId'));
        $report_id = trim($request->get('reportId'));
        list($reports_data, $headers) = $this->prepareReport($patient_id, $report_id);

        // Show template on the basic user role.
        $current_user = $this->get('security.token_storage')->getToken()->getUser();
        $roles = $current_user->getRoles();

        if (in_array('ROLE_PATIENT', $roles)) {
             return $this->render('PathologyBundle:Report:view_report_patient.html.twig', array(
                'reportsData' => $reports_data,
                'headers' => $headers
            ));
        }

        return $this->render('PathologyBundle:Report:view_report.html.twig', array(
            'reportsData' => $reports_data,
            'headers' => $headers
        ));
    }

    /**
     * Edit Report Action.
     */
    public function editReportAction(Request $request)
    {
        $report_id = trim($request->get('reportId'));
        $report = $this->getDoctrine()
            ->getRepository('PathologyBundle:Report')
            ->find($report_id);

        // Build the form.
        $form = $this->createForm(ReportType::class, $report);

        // Retrieve patient details.
        $patient_id = trim($request->get('patientId'));
        $patient = $this->getDoctrine()
            ->getRepository('PathologyBundle:User')
            ->find($patient_id);

        // Show patients and test details.
        $patient_details['name'] = $patient->getFname() . ' ' . $patient->getLname();
        $patient_details['email'] = $patient->getEmail();
        $patient_details['phone'] = $patient->getPhoneNumber();

        // Retrieve existing test for given patient.
        $tests = $patient->getPathologyTest();
        $test_reports = $report->getTestReports();

        // Set default previous tests values to given paitent's report.
        foreach ($tests as $test) {
            $test_id = $test->getId();
            $patient_details['tests'][$test_id] = array(
                'name' => $test->getName(),
                'referenceValue' => $test->getReferenceValue(),
                'unit' => $test->getUnit(),
                'day1' => time(),
                'day2' => strtotime('+1 day'),
                'day3' => strtotime('+2 day'),
                'id' => $test_id,
            );
            $form->add('day1_' . $test_id, TextType::class, array(
                'mapped' => false,
                'data' => $test_reports[$test_id]['day1']['value'],
            ));
            $form->add('day2_' . $test_id, TextType::class, array(
                'mapped' => false,
                'data' => $test_reports[$test_id]['day2']['value'],
            ));
            $form->add('day3_' . $test_id, TextType::class, array(
                'mapped' => false,
                'data' => $test_reports[$test_id]['day3']['value'],
            ));

            // Set Hidden test taken date.
            $form->add('day1_hidden_' . $test_id, HiddenType::class, array(
                'mapped' => false,
                'data' => $test_reports[$test_id]['day1']['timestamp'],
            ));
            $form->add('day2_hidden_' . $test_id, HiddenType::class, array(
                'mapped' => false,
                'data' => $test_reports[$test_id]['day2']['timestamp'],
            ));
            $form->add('day3_hidden_' . $test_id, HiddenType::class, array(
                'mapped' => false,
                'data' => $test_reports[$test_id]['day3']['timestamp'],
            ));
        }

        // Handle the submit (will only happen on POST).
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Update patient's tests reports with new values.
            foreach ($tests as $test) {
                $test_id = $test->getId();
                $reports_data[$test->getId()] = array(
                    'name' => $test->getName(),
                    'referenceValue' => $test->getReferenceValue(),
                    'unit' => $test->getUnit(),
                    'day1' => array(
                        'timestamp' => $form['day1_hidden_' . $test_id]->getData(),
                        'value' => $form['day1_' . $test_id]->getData(),
                    ),
                    'day2' => array(
                        'timestamp' => $form['day2_hidden_' . $test_id]->getData(),
                        'value' => $form['day2_' . $test_id]->getData(),
                    ),
                    'day3' => array(
                        'timestamp' => $form['day3_hidden_' . $test_id]->getData(),
                        'value' => $form['day3_' . $test_id]->getData(),
                    ),
                );
            }

            // Set Reports fields.
            $report->setPatient($patient);
            $report->setTestReports($reports_data);
            $time = new \DateTime();
            $report->setUpdated($time);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('view_patients');
        }

        return $this->render('PathologyBundle:Report:create_report.html.twig', array(
            'form' => $form->createView(),
            'patientDetails' => $patient_details,
        ));
    }

    /**
     * Remove report.
     */
    public function deleteReportAction(Request $request)
    {
        $report_id = trim($request->get('reportId'));
        $patient_id = trim($request->get('patientId'));
        $docrtine = $this->getDoctrine();
        $report = $docrtine->getRepository('PathologyBundle:Report')
            ->find($report_id);

        if (!empty($report)) {
            $em = $docrtine->getManager();
            $em->remove($report);
            $em->flush();
        }

        return $this->redirectToRoute('view_reports', array('patientId' => $patient_id));
    }

    public function exportReportToPdfAction(Request $request)
    {
        $patient_id = trim($request->get('patientId'));
        $report_id = trim($request->get('reportId'));
        list($reports_data, $headers) = $this->prepareReport($patient_id, $report_id);

        $html = $this->renderView('PathologyBundle:Report:generate_report.html.twig', array(
            'reportsData' => $reports_data,
            'headers' => $headers
        ));

        $file_name = $reports_data['patient_name'] . '_' . time() . '.pdf';
        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="{$file_name}"'
            )
        );
    }

    /**
     * Send Report as email to patient's email address.
     */
    function mailReportToPdfAction(Request $request)
    {
        $patient_id = trim($request->get('patientId'));
        $report_id = trim($request->get('reportId'));
        list($reports_data, $headers) = $this->prepareReport($patient_id, $report_id);
        $from = $this->getParameter('from_mail');

        // Load current user and redirect to appropriate template on the basis of role.
        $current_user = $this->get('security.token_storage')->getToken()->getUser();

        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom($from)
            ->setTo($current_user->getEmail())
            ->setBody(
                $this->renderView('PathologyBundle:Report:generate_report.html.twig', array(
                    'reportsData' => $reports_data,
                    'headers' => $headers
                )),
                'text/html'
            );
        $this->get('mailer')->send($message);

        return $this->redirectToRoute('view_reports', array(
            'patientId' => $patient_id,
            'message' => 'Report has been sent to your mail address ' . $current_user->getEmail() . '.',
        ));
    }

    /**
     * Prepare patient reports.
     */
    private function prepareReport($patient_id, $report_id)
    {
        $headers = array();
        $reports_data = array();

        $patient = $this->getDoctrine()
            ->getRepository('PathologyBundle:User')
            ->find($patient_id);

        // Retrieve report details.
        $report = $this->getDoctrine()
            ->getRepository('PathologyBundle:Report')
            ->find($report_id);

        $reports_data['patient_name'] = $patient->getFname() . ' ' . $patient->getLname();
        $reports_data['email'] = $patient->getEmail();
        $reports_data['phone'] = $patient->getPhoneNumber();
        $reports_data['created'] = $report->getCreated();
        $reports_data['created'] = $report->getUpdated();

        $test_reports = $report->getTestReports();
        $reports_data['test_reports'] = $test_reports;
        foreach ($test_reports as $test_report) {
            $headers['day1'] = $test_report['day1']['timestamp'];
            $headers['day2'] = $test_report['day2']['timestamp'];
            $headers['day3'] = $test_report['day3']['timestamp'];
        }

        return array($reports_data, $headers);
    }

}
